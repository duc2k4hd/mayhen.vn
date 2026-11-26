/* ==========================================================================
   MÂY HẸN – THE ART OF TAROT (/tarot)
   100% Pure Vanilla JavaScript — Zero External Dependencies
   - Fine Art SVG Generator for all 78 Rider-Waite-Smith Cards + 180° Symmetric Back
   - 3D Shuffle, 3D Fan/Swipe Selection, 3D 180° Cinematic Flip
   - Web Audio Paper Sound Engine (OFF by default)
   - HiDPI PNG Art Card Exporter (1:1, 4:5, 9:16) with Vietnamese-safe fonts
   ========================================================================== */

(function () {
    'use strict';

    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    /* ----------------------------------------------------------------------
       0. Full-Screen Quiet Luxury Analysis Overlay (Khóa tương tác khi luận giải)
       ---------------------------------------------------------------------- */
    const AnalysisOverlay = {
        el: null,
        timer: null,
        stages: [
            'Đang khóa dữ liệu các lá bài đã rút & đối chiếu mã xác thực SHA-256…',
            'Đang phân tích biểu tượng hình ảnh & chiều Xuôi/Ngược chuẩn Rider–Waite–Smith…',
            'Đang kết nối tương tác giữa các vị trí bài & cân bằng 4 Nguyên tố…',
            'Đang biên soạn bản luận giải 6 tầng & gợi ý hành động thực tế cho bạn…',
        ],
        ensureDom() {
            if (this.el) return this.el;
            const wrap = document.createElement('div');
            wrap.id = 'mayhenTarotLoadingOverlay';
            wrap.className = 'mayhen_tarot_loading_overlay';
            wrap.setAttribute('role', 'dialog');
            wrap.setAttribute('aria-modal', 'true');
            wrap.setAttribute('aria-live', 'polite');
            wrap.innerHTML = `
                <div class="mayhen_tarot_loading_modal">
                    <div class="mayhen_tarot_overlay_emblem" aria-hidden="true">
                        <svg viewBox="0 0 132 132" xmlns="http://www.w3.org/2000/svg">
                            <g class="mayhen_tarot_overlay_ring_outer">
                                <circle cx="66" cy="66" r="60" fill="none" stroke="#C8A055" stroke-width="1.2" stroke-dasharray="4 6"/>
                                <circle cx="66" cy="6" r="3" fill="#E7D2A3"/>
                                <circle cx="66" cy="126" r="3" fill="#E7D2A3"/>
                            </g>
                            <g class="mayhen_tarot_overlay_ring_inner">
                                <circle cx="66" cy="66" r="46" fill="none" stroke="rgba(231,210,163,0.45)" stroke-width="1"/>
                                <polygon points="66,22 72,60 110,66 72,72 66,110 60,72 22,66 60,60" fill="none" stroke="#C8A055" stroke-width="0.9" stroke-opacity="0.7"/>
                            </g>
                            <g class="mayhen_tarot_overlay_card_float">
                                <rect x="49" y="39" width="34" height="54" rx="4" fill="#FAF5EB" stroke="#C8A055" stroke-width="1.6"/>
                                <rect x="53" y="43" width="26" height="46" rx="2" fill="none" stroke="#85182A" stroke-width="0.8"/>
                                <polygon points="66,54 69,63 78,66 69,69 66,78 63,69 54,66 63,63" fill="#85182A" fill-opacity="0.85"/>
                            </g>
                        </svg>
                    </div>
                    <div class="mayhen_tarot_overlay_eyebrow">✦ MÂY HẸN – THE ART OF TAROT</div>
                    <h3 class="mayhen_tarot_overlay_title" id="tarotOverlayTitle">Đang Luận Giải Trải Bài</h3>
                    <p class="mayhen_tarot_overlay_stage_text" id="tarotOverlayStageText"></p>
                    <div class="mayhen_tarot_overlay_dots" id="tarotOverlayDots">
                        <span class="mayhen_tarot_overlay_dot is-active"></span>
                        <span class="mayhen_tarot_overlay_dot"></span>
                        <span class="mayhen_tarot_overlay_dot"></span>
                        <span class="mayhen_tarot_overlay_dot"></span>
                    </div>
                    <div class="mayhen_tarot_overlay_cards_pill" id="tarotOverlayCardsPill" style="display:none;"></div>
                    <p class="mayhen_tarot_overlay_note">Vui lòng giữ nguyên trang trong giây lát — hệ thống tạm khóa các nút để bảo toàn quá trình luận giải.</p>
                </div>
            `;
            document.body.appendChild(wrap);
            this.el = wrap;
            return wrap;
        },
        show(title = 'Đang Luận Giải Trải Bài', cardNames = []) {
            const el = this.ensureDom();
            const titleEl = el.querySelector('#tarotOverlayTitle');
            const stageEl = el.querySelector('#tarotOverlayStageText');
            const dots = Array.from(el.querySelectorAll('.mayhen_tarot_overlay_dot'));
            const pillEl = el.querySelector('#tarotOverlayCardsPill');

            if (titleEl) titleEl.textContent = title;
            if (pillEl) {
                if (cardNames && cardNames.length) {
                    pillEl.textContent = 'Các lá đang phân tích: ' + cardNames.join(' · ');
                    pillEl.style.display = 'block';
                } else {
                    pillEl.style.display = 'none';
                }
            }

            let step = 0;
            const applyStep = (idx) => {
                if (stageEl) {
                    stageEl.style.opacity = '0.35';
                    setTimeout(() => {
                        stageEl.textContent = this.stages[idx] || this.stages[this.stages.length - 1];
                        stageEl.style.opacity = '1';
                    }, 120);
                }
                dots.forEach((d, i) => {
                    d.classList.toggle('is-active', i === idx);
                    d.classList.toggle('is-done', i < idx);
                });
            };

            applyStep(0);
            clearInterval(this.timer);
            this.timer = setInterval(() => {
                if (step < this.stages.length - 1) {
                    step++;
                    applyStep(step);
                }
            }, 2400);

            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(() => el.classList.add('is-visible'));
        },
        hide() {
            clearInterval(this.timer);
            this.timer = null;
            if (this.el) this.el.classList.remove('is-visible');
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
        },
    };

    /* ----------------------------------------------------------------------
       1. Web Audio Paper Sound Synthesizer (Optional, OFF by default)
       ---------------------------------------------------------------------- */
    const SoundEngine = {
        enabled: false,
        ctx: null,
        ensureCtx() {
            if (!this.ctx) {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (AudioCtx) {
                    this.ctx = new AudioCtx();
                }
            }
            if (this.ctx && this.ctx.state === 'suspended') {
                this.ctx.resume().catch(() => {});
            }
        },
        playPaper(duration = 0.09, freq = 1400, gainVal = 0.05) {
            if (!this.enabled) return;
            try {
                this.ensureCtx();
                if (!this.ctx) return;
                const sampleRate = this.ctx.sampleRate;
                const frameCount = Math.floor(sampleRate * duration);
                const buffer = this.ctx.createBuffer(1, frameCount, sampleRate);
                const data = buffer.getChannelData(0);
                for (let i = 0; i < frameCount; i++) {
                    const env = Math.sin((i / frameCount) * Math.PI);
                    data[i] = (Math.random() * 2 - 1) * env;
                }
                const src = this.ctx.createBufferSource();
                src.buffer = buffer;
                const filter = this.ctx.createBiquadFilter();
                filter.type = 'bandpass';
                filter.frequency.value = freq;
                filter.Q.value = 1.4;
                const gain = this.ctx.createGain();
                gain.gain.value = gainVal;
                src.connect(filter);
                filter.connect(gain);
                gain.connect(this.ctx.destination);
                src.start();
            } catch (e) {
                // Silent fallback if browser blocks Web Audio
            }
        },
        shuffle() {
            this.playPaper(0.14, 1100, 0.045);
            setTimeout(() => this.playPaper(0.12, 1350, 0.04), 150);
            setTimeout(() => this.playPaper(0.14, 1200, 0.045), 310);
        },
        pick() {
            this.playPaper(0.08, 1750, 0.05);
        },
        flip() {
            this.playPaper(0.11, 1500, 0.06);
        },
    };

    /* ----------------------------------------------------------------------
       2. Fine Art SVG Generator (Symmetric 180° Back + 78 RWS Front Cards)
       ---------------------------------------------------------------------- */
    function escapeXml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function buildTarotBackSvg() {
        return `
        <svg viewBox="0 0 220 366" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Mặt sau lá bài Mây Hẹn">
            <defs>
                <linearGradient id="mhBackGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#1A2542"/>
                    <stop offset="50%" stop-color="#10182C"/>
                    <stop offset="100%" stop-color="#1A2542"/>
                </linearGradient>
            </defs>
            <rect width="220" height="366" rx="12" fill="url(#mhBackGrad)"/>
            <rect x="8" y="8" width="204" height="350" rx="8" fill="none" stroke="#D9B464" stroke-width="1.4"/>
            <rect x="14" y="14" width="192" height="338" rx="6" fill="none" stroke="#D9B464" stroke-width="0.6" stroke-opacity="0.6"/>
            <path d="M22 22 L38 22 M22 22 L22 38 M198 22 L182 22 M198 22 L198 38 M22 344 L38 344 M22 344 L22 328 M198 344 L182 344 M198 344 L198 328" stroke="#F2DFB3" stroke-width="1.2"/>
            <circle cx="110" cy="183" r="64" fill="none" stroke="#D9B464" stroke-width="1" stroke-opacity="0.78"/>
            <circle cx="110" cy="183" r="46" fill="none" stroke="#F2DFB3" stroke-width="0.8" stroke-dasharray="3 4"/>
            <polygon points="110,126 124,169 167,183 124,197 110,240 96,197 53,183 96,169" fill="none" stroke="#F2DFB3" stroke-width="1.2"/>
            <circle cx="110" cy="183" r="14" fill="#141C31" stroke="#D9B464" stroke-width="1.4"/>
            <circle cx="110" cy="183" r="4.5" fill="#F2DFB3"/>
            <path d="M72 74 Q110 52 148 74 Q110 96 72 74 Z" fill="none" stroke="#D9B464" stroke-width="1" stroke-opacity="0.8"/>
            <path d="M72 292 Q110 270 148 292 Q110 314 72 292 Z" fill="none" stroke="#D9B464" stroke-width="1" stroke-opacity="0.8"/>
            <circle cx="110" cy="74" r="3" fill="#D9B464"/>
            <circle cx="110" cy="292" r="3" fill="#D9B464"/>
        </svg>`;
    }

    function suitColor(suit) {
        switch (suit) {
            case 'wands': return '#9A3412';
            case 'cups': return '#1D4ED8';
            case 'swords': return '#334155';
            case 'pentacles': return '#166534';
            default: return '#85182A';
        }
    }

    function suitGlyph(suit, cx, cy, scale = 1) {
        const col = suitColor(suit);
        if (suit === 'wands') {
            return `<g transform="translate(${cx},${cy}) scale(${scale})" stroke="${col}" fill="none" stroke-width="1.8">
                <line x1="0" y1="-15" x2="0" y2="15" stroke-linecap="round"/>
                <path d="M0 -7 C5 -10 7 -6 4 -2 M0 3 C-5 0 -7 4 -4 8" stroke="#1E4D3B" stroke-width="1.3"/>
            </g>`;
        }
        if (suit === 'cups') {
            return `<g transform="translate(${cx},${cy}) scale(${scale})" stroke="${col}" fill="rgba(29,78,216,0.08)" stroke-width="1.6">
                <path d="M-9 -10 H9 L6 2 C5 6 -5 6 -6 2 Z"/>
                <line x1="0" y1="5" x2="0" y2="12"/>
                <line x1="-6" y1="12" x2="6" y2="12"/>
            </g>`;
        }
        if (suit === 'swords') {
            return `<g transform="translate(${cx},${cy}) scale(${scale})" stroke="${col}" fill="none" stroke-width="1.6">
                <line x1="0" y1="-15" x2="0" y2="13"/>
                <line x1="-7" y1="5" x2="7" y2="5"/>
                <polygon points="0,-17 -2.5,-11 2.5,-11" fill="${col}"/>
            </g>`;
        }
        return `<g transform="translate(${cx},${cy}) scale(${scale})" stroke="#B88E44" fill="rgba(200,160,85,0.14)" stroke-width="1.5">
            <circle cx="0" cy="0" r="11"/>
            <polygon points="0,-8 2.4,-2.5 8,-2.5 3.5,1 5.2,7 0,3.5 -5.2,7 -3.5,1 -8,-2.5 -2.4,-2.5" fill="none" stroke="${col}" stroke-width="1.2"/>
        </g>`;
    }

    function buildMinorPipComposition(suit, number) {
        const coordsMap = {
            1: [[110, 174]],
            2: [[110, 134], [110, 214]],
            3: [[110, 126], [82, 206], [138, 206]],
            4: [[80, 136], [140, 136], [80, 212], [140, 212]],
            5: [[80, 132], [140, 132], [110, 174], [80, 216], [140, 216]],
            6: [[80, 126], [140, 126], [80, 174], [140, 174], [80, 222], [140, 222]],
            7: [[80, 122], [140, 122], [110, 152], [80, 184], [140, 184], [80, 224], [140, 224]],
            8: [[80, 118], [140, 118], [80, 155], [140, 155], [80, 192], [140, 192], [80, 229], [140, 229]],
            9: [[76, 118], [144, 118], [76, 154], [144, 154], [110, 174], [76, 194], [144, 194], [76, 230], [144, 230]],
            10: [[76, 114], [144, 114], [110, 134], [76, 160], [144, 160], [76, 194], [144, 194], [110, 214], [76, 234], [144, 234]],
        };
        if (number >= 11) {
            return `
                <circle cx="110" cy="174" r="54" fill="none" stroke="#C8A055" stroke-width="1.2"/>
                <circle cx="110" cy="174" r="44" fill="none" stroke="rgba(200,160,85,0.45)" stroke-width="0.8" stroke-dasharray="3 3"/>
                <path d="M78 130 L94 112 L110 126 L126 112 L142 130 Z" fill="rgba(200,160,85,0.22)" stroke="#B88E44" stroke-width="1.4"/>
                ${suitGlyph(suit, 110, 178, 1.85)}
            `;
        }
        const pts = coordsMap[number] || [[110, 174]];
        const scale = number === 1 ? 2.1 : (number <= 4 ? 1.15 : (number <= 7 ? 0.96 : 0.82));
        return pts.map(([x, y]) => suitGlyph(suit, x, y, scale)).join('');
    }

    function buildMajorEmblem(cardId, roman) {
        const accent = '#85182A';
        const gold = '#B88E44';
        const num = parseInt((cardId || '').replace(/\D+/g, ''), 10) || 0;
        const inner = [
            `<circle cx="110" cy="158" r="24" fill="rgba(200,160,85,0.16)" stroke="${gold}" stroke-width="1.5"/><path d="M72 222 Q110 184 148 222" fill="none" stroke="${accent}" stroke-width="1.5"/>`,
            `<polygon points="110,118 148,174 110,230 72,174" fill="rgba(200,160,85,0.1)" stroke="${gold}" stroke-width="1.4"/><circle cx="110" cy="174" r="14" fill="none" stroke="${accent}" stroke-width="1.4"/>`,
            `<path d="M110 122 A48 48 0 1 0 110 226 A34 34 0 1 1 110 122 Z" fill="rgba(200,160,85,0.2)" stroke="${gold}" stroke-width="1.4"/>`,
            `<circle cx="110" cy="174" r="46" fill="none" stroke="${gold}" stroke-width="1.4"/><polygon points="110,132 122,162 154,162 128,180 138,210 110,192 82,210 92,180 66,162 98,162" fill="rgba(133,24,42,0.12)" stroke="${accent}" stroke-width="1.3"/>`,
        ][num % 4];

        return `
            <rect x="34" y="88" width="152" height="172" rx="76" fill="rgba(200,160,85,0.06)" stroke="#C8A055" stroke-width="1.1"/>
            <line x1="110" y1="98" x2="110" y2="250" stroke="rgba(200,160,85,0.28)" stroke-width="0.9"/>
            <line x1="48" y1="174" x2="172" y2="174" stroke="rgba(200,160,85,0.28)" stroke-width="0.9"/>
            ${inner}
            <circle cx="110" cy="242" r="3" fill="#C8A055"/>
        `;
    }

    function buildTarotCardSvg(card) {
        if (!card) return buildTarotBackSvg();
        const isReversed = card.orientation === 'reversed' || card.is_reversed === true;
        const nameEn = card.name_en || card.card_id || 'Tarot';
        const nameVi = card.name_vi || '';
        const slug = card.slug || '';

        if (slug) {
            const imgSrc = `/assets/client/images/tarot/rws/${encodeURIComponent(slug)}.jpg`;
            const revClass = isReversed ? ' is-reversed' : '';
            const alt = escapeXml(`Lá bài Tarot ${nameEn}${nameVi ? ' (' + nameVi + ')' : ''}${isReversed ? ' — Lá Ngược' : ' — Lá Xuôi'}`);
            return `<div class="mayhen_tarot_real_card_wrap${revClass}"><img src="${imgSrc}" alt="${alt}" class="mayhen_tarot_real_card_img" loading="eager" decoding="async"></div>`;
        }

        const roman = card.roman || '';
        const hasOrient = Boolean(card.orientation) || typeof card.is_reversed === 'boolean';
        const arcana = card.arcana || (String(card.card_id || '').startsWith('major') ? 'major' : 'minor');
        const suit = card.suit || 'major';
        const number = Number(card.number || 1);

        const artwork = arcana === 'major'
            ? buildMajorEmblem(card.card_id, roman)
            : buildMinorPipComposition(suit, number);

        const artTransform = isReversed ? ' transform="rotate(180 110 174)"' : '';
        const orientPill = isReversed
            ? `<rect x="64" y="274" width="92" height="19" rx="9.5" fill="#85182A"/><text x="110" y="287" text-anchor="middle" font-size="9.5" font-weight="700" fill="#FAF5EB" font-family="Segoe UI, Arial, sans-serif">LÁ NGƯỢC</text>`
            : `<rect x="68" y="274" width="84" height="19" rx="9.5" fill="#142D25"/><text x="110" y="287" text-anchor="middle" font-size="9.5" font-weight="700" fill="#E7D2A3" font-family="Segoe UI, Arial, sans-serif">LÁ XUÔI</text>`;

        return `
        <svg viewBox="0 0 220 366" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="${escapeXml(nameEn)}">
            <rect width="220" height="366" rx="12" fill="#FAF5EB"/>
            <rect x="8" y="8" width="204" height="350" rx="8" fill="none" stroke="#C8A055" stroke-width="1.5"/>
            <rect x="13" y="13" width="194" height="340" rx="6" fill="none" stroke="rgba(41,37,36,0.16)" stroke-width="0.8"/>
            <text x="110" y="38" text-anchor="middle" font-size="14" font-weight="700" letter-spacing="2" fill="#85182A" font-family="Times New Roman, serif">${escapeXml(roman)}</text>
            <line x1="44" y1="48" x2="176" y2="48" stroke="#C8A055" stroke-width="0.9"/>
            <g${artTransform}>
                ${artwork}
            </g>
            ${hasOrient ? orientPill : ''}
            <line x1="36" y1="304" x2="184" y2="304" stroke="#C8A055" stroke-width="0.9"/>
            <text x="110" y="324" text-anchor="middle" font-size="13.5" font-weight="700" fill="#292524" font-family="Times New Roman, serif">${escapeXml(nameEn)}</text>
            <text x="110" y="342" text-anchor="middle" font-size="10.5" fill="#57534E" font-family="Segoe UI, Arial, sans-serif">${escapeXml(nameVi)}</text>
        </svg>`;
    }

    function hydrateCardArtworks() {
        document.querySelectorAll('[data-tarot-card-art]').forEach((el) => {
            const raw = el.getAttribute('data-tarot-card-art');
            if (!raw) return;
            try {
                const card = JSON.parse(raw);
                el.innerHTML = buildTarotCardSvg(card);
            } catch (e) {
                el.innerHTML = buildTarotBackSvg();
            }
        });
        document.querySelectorAll('[data-tarot-card-back]').forEach((el) => {
            el.innerHTML = buildTarotBackSvg();
        });
    }

    /* ----------------------------------------------------------------------
       3. Interactive 3D Reading Table Controller (/tarot)
       ---------------------------------------------------------------------- */
    function initLandingReadingTable() {
        const root = document.getElementById('mayhenTarotApp');
        if (!root) return;

        const configScript = document.getElementById('mayhenTarotConfig');
        if (!configScript) return;
        const cfg = JSON.parse(configScript.textContent || '{}');

        const state = {
            spreadId: cfg.presetSpread || 'three_love',
            category: cfg.presetCategory || 'love',
            orientationMode: 'upright_reversed',
            selectionMode: 'interactive',
            sessionToken: null,
            requiredCount: 3,
            positions: [],
            selectedSlots: [],
            revealedCards: [],
            isBusy: false,
        };

        const spreadMap = {};
        const spreadList = Array.isArray(cfg.spreads) ? cfg.spreads : Object.values(cfg.spreads || {});
        spreadList.forEach((sp) => {
            if (sp && sp.spread_id) spreadMap[sp.spread_id] = sp;
        });

        // Sound & Performance controls
        const soundBtn = document.getElementById('tarotSoundToggle');
        if (soundBtn) {
            soundBtn.addEventListener('click', () => {
                SoundEngine.enabled = !SoundEngine.enabled;
                soundBtn.setAttribute('aria-pressed', SoundEngine.enabled ? 'true' : 'false');
                soundBtn.textContent = SoundEngine.enabled ? '🔊 Âm thanh lá bài: BẬT' : '🔇 Âm thanh lá bài: TẮT';
                if (SoundEngine.enabled) SoundEngine.pick();
            });
        }

        const perfSelect = document.getElementById('tarotPerfMode');
        if (perfSelect) {
            perfSelect.addEventListener('change', () => {
                root.classList.toggle('mayhen_tarot_perf_lightweight', perfSelect.value === 'lightweight');
            });
        }

        // Mode Tabs (Online 3D vs Manual Physical Deck)
        const modeTabs = root.querySelectorAll('[data-tarot-mode-tab]');
        const onlinePanel = document.getElementById('tarotOnlinePanel');
        const manualPanel = document.getElementById('tarotManualPanel');
        modeTabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const mode = tab.getAttribute('data-tarot-mode-tab');
                modeTabs.forEach((t) => t.classList.toggle('is-active', t === tab));
                if (onlinePanel) onlinePanel.style.display = mode === 'online' ? 'block' : 'none';
                if (manualPanel) manualPanel.classList.toggle('is-active', mode === 'manual');
                if (mode === 'manual') renderManualSlots();
            });
        });

        // Spread selector
        const spreadBtns = root.querySelectorAll('[data-tarot-spread-id]');
        const tierBtns = root.querySelectorAll('[data-tarot-tier]');
        const manualSpreadSel = document.getElementById('tarotManualSpread');
        const manualRowsWrap = document.getElementById('tarotManualRows');
        const manualSubmitBtn = document.getElementById('tarotManualSubmitBtn');
        const startCtaBtn = document.getElementById('tarotStartSessionBtn');

        function applyTierFilter(tierKey, autoSelectFirst = false) {
            tierBtns.forEach((tb) => tb.classList.toggle('is-active', tb.getAttribute('data-tarot-tier') === tierKey));
            let firstVisibleSpreadId = null;
            spreadBtns.forEach((b) => {
                const match = b.getAttribute('data-tarot-spread-tier') === tierKey;
                b.classList.toggle('is-tier-hidden', !match);
                if (match && !firstVisibleSpreadId) {
                    firstVisibleSpreadId = b.getAttribute('data-tarot-spread-id');
                }
            });
            if (autoSelectFirst && firstVisibleSpreadId) {
                selectSpread(firstVisibleSpreadId, false);
            }
        }

        function selectSpread(spreadId, syncTier = true) {
            state.spreadId = spreadId;
            let matchedTier = '3';
            let matchedCount = 3;
            spreadBtns.forEach((b) => {
                const isSel = b.getAttribute('data-tarot-spread-id') === spreadId;
                b.classList.toggle('is-selected', isSel);
                if (isSel) {
                    matchedTier = b.getAttribute('data-tarot-spread-tier') || '3';
                    matchedCount = Number(b.getAttribute('data-tarot-card-count') || 3);
                }
            });
            if (syncTier) {
                applyTierFilter(matchedTier, false);
            }
            if (startCtaBtn) {
                const labelSuffix = matchedCount === 5 ? '5 Lá Chuyên Sâu' : `${matchedCount} Lá`;
                startCtaBtn.textContent = `✦ Xáo 78 Lá & Tự Tay Chọn ${labelSuffix}`;
            }
            if (manualSpreadSel && manualSpreadSel.value !== spreadId) {
                manualSpreadSel.value = spreadId;
            }
            renderManualSlots();
        }
        tierBtns.forEach((tb) => {
            tb.addEventListener('click', () => applyTierFilter(tb.getAttribute('data-tarot-tier'), true));
        });
        spreadBtns.forEach((btn) => {
            btn.addEventListener('click', () => selectSpread(btn.getAttribute('data-tarot-spread-id'), false));
        });
        selectSpread(state.spreadId, true);

        // Alert helper
        const alertBox = document.getElementById('tarotAlertBox');
        function showAlert(msg) {
            if (!alertBox) return;
            alertBox.textContent = msg;
            alertBox.classList.add('is-visible');
        }
        function hideAlert() {
            if (!alertBox) return;
            alertBox.textContent = '';
            alertBox.classList.remove('is-visible');
        }

        // AI Question Refiner
        const refineBtn = document.getElementById('tarotRefineQuestionBtn');
        const refineBox = document.getElementById('tarotRefineBox');
        const questionInput = document.getElementById('tarotQuestionInput');
        if (refineBtn && refineBox && questionInput) {
            refineBtn.addEventListener('click', async () => {
                hideAlert();
                const q = questionInput.value.trim();
                if (q.length < 4) {
                    showAlert('Hãy nhập ngắn gọn điều bạn đang băn khoăn (tối thiểu 4 ký tự) để Mây Hẹn gợi ý câu hỏi mở.');
                    return;
                }
                refineBtn.disabled = true;
                refineBtn.textContent = 'Đang gợi ý...';
                try {
                    const res = await fetch(cfg.endpoints.suggestQuestion, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        body: JSON.stringify({ question: q }),
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        showAlert(data.message || 'Không thể gợi ý câu hỏi lúc này.');
                    } else {
                        const list = Array.isArray(data.suggestions)
                            ? data.suggestions
                            : (data.suggested_question ? [data.suggested_question] : []);
                        const pills = list.map((s) =>
                            `<button type="button" class="mayhen_tarot_refine_pill">${escapeXml(s)}</button>`
                        ).join('');
                        refineBox.innerHTML = `<div><strong>Gợi ý làm rõ câu hỏi:</strong> ${escapeXml(data.reason || '')}</div><div class="mayhen_tarot_refine_pills">${pills}</div>`;
                        refineBox.classList.add('is-visible');
                        refineBox.querySelectorAll('.mayhen_tarot_refine_pill').forEach((p) => {
                            p.addEventListener('click', () => {
                                questionInput.value = p.textContent;
                                refineBox.classList.remove('is-visible');
                            });
                        });
                    }
                } catch (err) {
                    showAlert('Kết nối tạm gián đoạn khi gợi ý câu hỏi.');
                } finally {
                    refineBtn.disabled = false;
                    refineBtn.textContent = 'Gợi ý câu hỏi mở';
                }
            });
        }

        // Stepper helper
        function setStep(stepNum) {
            root.querySelectorAll('[data-tarot-step]').forEach((el) => {
                const n = Number(el.getAttribute('data-tarot-step'));
                el.classList.toggle('is-active', n === stepNum);
                el.classList.toggle('is-done', n < stepNum);
            });
        }

        // Start Session (Shuffle 78 cards on Server via Fisher-Yates CSPRNG)
        const setupBox = document.getElementById('tarotSetupBox');
        const tableStage = document.getElementById('tarotTableStage');
        const shuffleArena = document.getElementById('tarotShuffleArena');
        const fanViewport = document.getElementById('tarotFanViewport');
        const fanArc = document.getElementById('tarotFanArc');
        const trayEl = document.getElementById('tarotTray');
        const statusTitle = document.getElementById('tarotTableStatus');
        const statusHint = document.getElementById('tarotTableHint');
        const hashPill = document.getElementById('tarotHashPill');
        const completeBtn = document.getElementById('tarotCompleteBtn');
        const resetBtn = document.getElementById('tarotResetBtn');
        const startBtn = document.getElementById('tarotStartSessionBtn');
        const quickBtn = document.getElementById('tarotQuickDrawBtn');

        async function startReading(selectionMode) {
            if (state.isBusy) return;
            hideAlert();
            state.isBusy = true;
            state.selectionMode = selectionMode;
            state.selectedSlots = [];
            state.revealedCards = [];

            const category = document.getElementById('tarotCategorySelect')?.value || 'love';
            const relationshipStage = document.getElementById('tarotStageSelect')?.value || '';
            const orientationMode = document.getElementById('tarotOrientationSelect')?.value || 'upright_reversed';
            const question = questionInput?.value.trim() || '';
            const contextNote = document.getElementById('tarotContextInput')?.value.trim() || '';

            if (startBtn) startBtn.disabled = true;
            if (quickBtn) quickBtn.disabled = true;

            try {
                const res = await fetch(cfg.endpoints.createSession, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        spread_id: state.spreadId,
                        orientation_mode: orientationMode,
                        selection_mode: selectionMode,
                        category,
                        relationship_stage: relationshipStage || null,
                        question: question || null,
                        context_note: contextNote || null,
                    }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    showAlert(data.message || 'Không thể khởi tạo phiên trải bài.');
                    return;
                }

                state.sessionToken = data.session_token;
                state.requiredCount = Number(data.required_count || data.card_count || 3);
                state.positions = (data.spread && data.spread.positions) ? data.spread.positions : (data.positions || []);

                if (setupBox) setupBox.style.display = 'none';
                if (tableStage) tableStage.classList.add('is-active');
                if (hashPill) {
                    hashPill.textContent = `SHA-256 Deck Lock: ${String(data.commitment_hash || '').slice(0, 18)}… · 78 lá RWS`;
                }
                if (completeBtn) completeBtn.style.display = 'none';

                setStep(2);
                renderTraySlots();
                await runShuffleAnimation();

                if (selectionMode === 'quick') {
                    setStep(3);
                    if (statusTitle) statusTitle.textContent = 'Đang rút ngẫu nhiên không hoàn lại...';
                    if (statusHint) statusHint.textContent = 'Hệ thống lấy đúng vị trí đã khóa từ bộ 78 lá Rider–Waite–Smith.';
                    await autoSelectQuickSlots(data.quick_cards || []);
                } else {
                    setStep(3);
                    renderFanDeck();
                    updateSelectionPrompt();
                }
            } catch (err) {
                showAlert('Kết nối tạm gián đoạn khi khởi tạo bàn trải bài.');
            } finally {
                state.isBusy = false;
                if (startBtn) startBtn.disabled = false;
                if (quickBtn) quickBtn.disabled = false;
            }
        }

        function runShuffleAnimation() {
            return new Promise((resolve) => {
                if (shuffleArena) {
                    shuffleArena.style.display = 'flex';
                    shuffleArena.classList.add('is-shuffling');
                }
                if (fanViewport) fanViewport.style.display = 'none';
                if (statusTitle) statusTitle.textContent = 'Đang xáo bộ 78 lá Rider–Waite–Smith...';
                if (statusHint) statusHint.textContent = 'Thở chậm một nhịp và giữ tâm trí hướng về điều bạn muốn soi chiếu.';
                SoundEngine.shuffle();
                const isLight = root.classList.contains('mayhen_tarot_perf_lightweight');
                setTimeout(() => {
                    if (shuffleArena) {
                        shuffleArena.classList.remove('is-shuffling');
                        shuffleArena.style.display = 'none';
                    }
                    resolve();
                }, isLight ? 450 : 1450);
            });
        }

        function renderTraySlots() {
            if (!trayEl) return;
            const backSvg = buildTarotBackSvg();
            trayEl.innerHTML = state.positions.map((pos, idx) => `
                <div class="mayhen_tarot_tray_slot" data-tray-index="${idx}">
                    <div class="mayhen_tarot_flip_scene">
                        <div class="mayhen_tarot_flip_card" id="tarotTrayFlip_${idx}">
                            <div class="mayhen_tarot_flip_face">${backSvg}</div>
                            <div class="mayhen_tarot_flip_face mayhen_tarot_flip_front" id="tarotTrayFront_${idx}"></div>
                        </div>
                    </div>
                    <div class="mayhen_tarot_slot_caption">
                        <strong>${idx + 1}. ${escapeXml(pos.label || pos.name || '')}</strong>
                        <div class="mayhen_tarot_slot_card_title" id="tarotTrayTitle_${idx}">Chờ chọn lá</div>
                    </div>
                </div>
            `).join('');
        }

        let ribbonOffset = 0;
        let hoveredRibbonIdx = -1;
        let lastSlideDir = null;

        function computeRibbonTransform(i, total, hoveredIdx, tiltX = 0, tiltY = 0) {
            const center = (total - 1) / 2;
            const d = i - center; // -6 .. +6
            const angleDeg = d * 6.4; // Vòng cung 3D rõ rệt ±38.4°
            const rad = (angleDeg * Math.PI) / 180;

            let x = Math.sin(rad) * 560;
            let y = (1 - Math.cos(rad)) * 285; // Độ cong vòng cung ~62px rõ đẹp
            let z = Math.cos(rad) * 140 - 110; // Lá giữa nhô ra phía trước 3D, cánh hai bên lùi sâu
            let rotY = -d * 5.6;
            let rotX = 6;
            let rotZ = d * 4.4;
            let scale = 0.94 + Math.cos(rad) * 0.1;

            if (hoveredIdx !== -1) {
                if (i === hoveredIdx) {
                    y -= 32;
                    z = 115;
                    rotY = tiltY;
                    rotX = tiltX;
                    rotZ = 0;
                    scale = 1.15;
                } else {
                    const dir = i < hoveredIdx ? -1 : 1;
                    const dist = Math.abs(i - hoveredIdx);
                    x += dir * Math.max(16, 44 - dist * 5);
                }
            }
            return `translate3d(${x.toFixed(1)}px, ${y.toFixed(1)}px, ${z.toFixed(1)}px) rotateX(${rotX.toFixed(1)}deg) rotateY(${rotY.toFixed(1)}deg) rotateZ(${rotZ.toFixed(1)}deg) scale(${scale.toFixed(3)})`;
        }

        function updateRibbonPositions() {
            if (!fanArc) return;
            const cards = Array.from(fanArc.querySelectorAll('.mayhen_tarot_fan_card'));
            const total = cards.length;
            cards.forEach((btn, i) => {
                if (btn.classList.contains('is-picked')) return;
                btn.classList.toggle('is-hovered', i === hoveredRibbonIdx);
                btn.style.transform = computeRibbonTransform(i, total, hoveredRibbonIdx);
                const centerDist = Math.abs(i - (total - 1) / 2);
                btn.style.zIndex = i === hoveredRibbonIdx ? '95' : String(Math.round(50 - centerDist * 3));
            });
        }

        function shiftRibbon(delta) {
            ribbonOffset = (ribbonOffset + delta + 78) % 78;
            lastSlideDir = delta > 0 ? 'right' : 'left';
            SoundEngine.pick();
            renderFanDeck();
        }

        function renderFanDeck() {
            if (!fanViewport || !fanArc) return;
            fanViewport.style.display = 'flex';
            const backSvg = buildTarotBackSvg();
            const visibleCount = 13;

            // Vẽ đường quỹ đạo vòng cung vàng tinh tế trên nền nhung
            if (!fanViewport.querySelector('.mayhen_tarot_arch_guide')) {
                const guide = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                guide.setAttribute('viewBox', '0 0 920 260');
                guide.setAttribute('class', 'mayhen_tarot_arch_guide');
                guide.setAttribute('aria-hidden', 'true');
                guide.innerHTML = `<path d="M 40 215 Q 460 -15 880 215" fill="none" stroke="#C8A055" stroke-width="1.2" stroke-dasharray="4 6"/><path d="M 90 232 Q 460 22 830 232" fill="none" stroke="#E7D2A3" stroke-width="0.7" stroke-opacity="0.5"/>`;
                fanViewport.prepend(guide);
            }

            // Lấy 13 lá chưa được rút tính từ ribbonOffset trên toàn bộ 78 lá thật
            const availableSlots = [];
            for (let k = 0; k < 78; k++) {
                const slot = (ribbonOffset + k) % 78;
                if (!state.selectedSlots.includes(slot)) {
                    availableSlots.push(slot);
                }
                if (availableSlots.length >= visibleCount) break;
            }

            hoveredRibbonIdx = -1;
            fanArc.innerHTML = availableSlots.map((realSlotIndex, i) => {
                const slotLabel = '#' + String(realSlotIndex + 1).padStart(2, '0');
                return `<button type="button" class="mayhen_tarot_fan_card" data-slot-index="${realSlotIndex}" data-ribbon-idx="${i}" aria-label="Chọn lá bài vị trí ${slotLabel}">${backSvg}<span class="mayhen_tarot_card_slot_tag">${slotLabel}</span><span class="mayhen_tarot_card_sheen"></span></button>`;
            }).join('');

            // Chạy hiệu ứng trượt 3D mượt khi bấm nút Trái / Phải
            fanArc.classList.remove('is-sliding-left', 'is-sliding-right');
            if (lastSlideDir) {
                void fanArc.offsetWidth;
                fanArc.classList.add(lastSlideDir === 'right' ? 'is-sliding-right' : 'is-sliding-left');
                lastSlideDir = null;
            }

            updateRibbonPositions();

            const total = availableSlots.length;
            fanArc.querySelectorAll('.mayhen_tarot_fan_card').forEach((btn, i) => {
                btn.addEventListener('mouseenter', () => {
                    hoveredRibbonIdx = i;
                    updateRibbonPositions();
                });
                btn.addEventListener('mousemove', (ev) => {
                    if (hoveredRibbonIdx !== i || btn.classList.contains('is-picked')) return;
                    const rect = btn.getBoundingClientRect();
                    const px = (ev.clientX - rect.left) / rect.width;
                    const py = (ev.clientY - rect.top) / rect.height;
                    btn.style.setProperty('--mx', `${(px * 100).toFixed(1)}%`);
                    btn.style.setProperty('--my', `${(py * 100).toFixed(1)}%`);
                    const tiltY = (px - 0.5) * 18;
                    const tiltX = (0.5 - py) * 14;
                    btn.style.transform = computeRibbonTransform(i, total, i, tiltX, tiltY);
                });
                btn.addEventListener('focus', () => {
                    hoveredRibbonIdx = i;
                    updateRibbonPositions();
                });
                btn.addEventListener('click', () => handlePickSlot(btn));
            });

            fanArc.onmouseleave = () => {
                hoveredRibbonIdx = -1;
                updateRibbonPositions();
            };

            // Hỗ trợ cuộn con lăn chuột trên vòng cung để trượt 78 lá
            if (!fanViewport.dataset.wheelBound) {
                fanViewport.dataset.wheelBound = '1';
                let wheelLock = false;
                fanViewport.addEventListener('wheel', (ev) => {
                    if (Math.abs(ev.deltaX) > 8 || ev.shiftKey) {
                        ev.preventDefault();
                        if (wheelLock) return;
                        wheelLock = true;
                        shiftRibbon((ev.deltaX || ev.deltaY) > 0 ? 4 : -4);
                        setTimeout(() => { wheelLock = false; }, 220);
                    }
                }, { passive: false });
            }

            // Thanh điều hướng trượt mượt trên vòng cung 78 lá
            let ctrl = fanViewport.querySelector('.mayhen_tarot_ribbon_controls');
            if (!ctrl) {
                ctrl = document.createElement('div');
                ctrl.className = 'mayhen_tarot_ribbon_controls';
                ctrl.innerHTML = `
                    <button type="button" class="mayhen_tarot_ribbon_nav_btn" data-ribbon-shift="-5">‹ Trượt sang trái</button>
                    <span class="mayhen_tarot_ribbon_meta" id="tarotRibbonMeta"></span>
                    <button type="button" class="mayhen_tarot_ribbon_nav_btn" data-ribbon-shift="5">Trượt sang phải ›</button>
                `;
                fanViewport.appendChild(ctrl);
                ctrl.querySelectorAll('[data-ribbon-shift]').forEach((navBtn) => {
                    navBtn.addEventListener('click', (ev) => {
                        ev.preventDefault();
                        ev.stopPropagation();
                        shiftRibbon(Number(navBtn.getAttribute('data-ribbon-shift') || 0));
                    });
                });
            }
            const metaEl = document.getElementById('tarotRibbonMeta');
            if (metaEl && availableSlots.length) {
                const firstNum = String(availableSlots[0] + 1).padStart(2, '0');
                const lastNum = String(availableSlots[availableSlots.length - 1] + 1).padStart(2, '0');
                metaEl.textContent = `Đang xem lá #${firstNum} – #${lastNum} · Còn ${78 - state.selectedSlots.length}/78 lá úp`;
            }
        }

        function updateSelectionPrompt() {
            const done = state.selectedSlots.length;
            const nextPos = state.positions[done];
            if (done < state.requiredCount && nextPos) {
                if (statusTitle) statusTitle.textContent = `Chọn lá ${done + 1}/${state.requiredCount}: ${nextPos.label || nextPos.name || ''}`;
                if (statusHint) statusHint.textContent = nextPos.meaning || nextPos.description || 'Chạm vào một lá bài bất kỳ trên bàn nhung để rút.';
            } else {
                if (statusTitle) statusTitle.textContent = `Đã lật đủ ${state.requiredCount}/${state.requiredCount} lá bài`;
                if (statusHint) statusHint.textContent = 'Nhấn nút bên dưới để Mây Hẹn tổng hợp luận giải 6 tầng cho trải bài của bạn.';
                if (fanViewport) fanViewport.style.display = 'none';
                if (completeBtn) completeBtn.style.display = 'inline-flex';
                setStep(4);
            }
        }

        async function handlePickSlot(btnEl) {
            if (state.isBusy || state.selectedSlots.length >= state.requiredCount) return;
            const slotIndex = Number(btnEl.getAttribute('data-slot-index'));
            if (state.selectedSlots.includes(slotIndex)) return;

            state.isBusy = true;
            btnEl.classList.add('is-picked');
            btnEl.disabled = true;
            SoundEngine.pick();

            try {
                const url = cfg.endpoints.selectSlotTemplate.replace('__TOKEN__', state.sessionToken);
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: JSON.stringify({ slot_index: slotIndex }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    btnEl.classList.remove('is-picked');
                    btnEl.disabled = false;
                    showAlert(data.message || 'Không thể chọn lá bài này.');
                    return;
                }

                const revealIdx = state.selectedSlots.length;
                state.selectedSlots.push(slotIndex);
                state.revealedCards.push(data.card);
                revealCardInTray(revealIdx, data.card);
                updateSelectionPrompt();
                if (state.selectedSlots.length < state.requiredCount) {
                    setTimeout(() => {
                        hoveredRibbonIdx = -1;
                        renderFanDeck();
                    }, 260);
                }
            } catch (err) {
                btnEl.classList.remove('is-picked');
                btnEl.disabled = false;
                showAlert('Kết nối tạm gián đoạn khi rút lá bài.');
            } finally {
                state.isBusy = false;
            }
        }

        async function autoSelectQuickSlots(quickCards = []) {
            if (Array.isArray(quickCards) && quickCards.length >= state.requiredCount) {
                for (let i = 0; i < state.requiredCount; i++) {
                    const card = quickCards[i];
                    state.selectedSlots.push(i);
                    state.revealedCards.push(card);
                    revealCardInTray(i, card);
                    await new Promise((r) => setTimeout(r, 280));
                }
                updateSelectionPrompt();
                return;
            }
            const url = cfg.endpoints.selectSlotTemplate.replace('__TOKEN__', state.sessionToken);
            for (let i = 0; i < state.requiredCount; i++) {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: JSON.stringify({ slot_index: i }),
                });
                const data = await res.json();
                if (res.ok && data.success && data.card) {
                    state.selectedSlots.push(i);
                    state.revealedCards.push(data.card);
                    revealCardInTray(i, data.card);
                    await new Promise((r) => setTimeout(r, 260));
                }
            }
            updateSelectionPrompt();
        }

        function revealCardInTray(idx, card) {
            const flipEl = document.getElementById(`tarotTrayFlip_${idx}`);
            const frontEl = document.getElementById(`tarotTrayFront_${idx}`);
            const titleEl = document.getElementById(`tarotTrayTitle_${idx}`);
            if (frontEl) frontEl.innerHTML = buildTarotCardSvg(card);
            if (flipEl) {
                flipEl.classList.add('is-filled');
                setTimeout(() => {
                    flipEl.classList.add('is-flipped');
                    SoundEngine.flip();
                }, 90);
            }
            if (titleEl) {
                const orient = card.orientation === 'reversed' ? 'Ngược' : 'Xuôi';
                titleEl.textContent = `${card.name_en} (${orient})`;
            }
        }

        if (completeBtn) {
            completeBtn.addEventListener('click', async () => {
                if (state.isBusy || !state.sessionToken) return;
                hideAlert();
                state.isBusy = true;
                completeBtn.disabled = true;
                if (resetBtn) resetBtn.disabled = true;
                const cardNames = state.revealedCards.map((c) => `${c.name_en}${c.orientation === 'reversed' ? ' (Ngược)' : ''}`);
                AnalysisOverlay.show('Đang Luận Giải 6 Tầng', cardNames);
                try {
                    const url = cfg.endpoints.completeSessionTemplate.replace('__TOKEN__', state.sessionToken);
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        body: JSON.stringify({}),
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        AnalysisOverlay.hide();
                        showAlert(data.message || 'Không thể hoàn tất luận giải.');
                        completeBtn.disabled = false;
                        if (resetBtn) resetBtn.disabled = false;
                        return;
                    }
                    window.location.href = data.redirect_url || data.result_url;
                } catch (err) {
                    AnalysisOverlay.hide();
                    showAlert('Kết nối tạm gián đoạn khi hoàn tất luận giải.');
                    completeBtn.disabled = false;
                    if (resetBtn) resetBtn.disabled = false;
                } finally {
                    state.isBusy = false;
                }
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                state.sessionToken = null;
                state.selectedSlots = [];
                if (tableStage) tableStage.classList.remove('is-active');
                if (setupBox) setupBox.style.display = 'block';
                setStep(1);
            });
        }

        if (startBtn) startBtn.addEventListener('click', () => startReading('interactive'));
        if (quickBtn) quickBtn.addEventListener('click', () => startReading('quick'));

        // Manual Physical Deck Input
        function renderManualSlots() {
            if (!manualRowsWrap) return;
            const spId = manualSpreadSel?.value || state.spreadId;
            const sp = spreadMap[spId] || spreadList[0];
            if (!sp) return;
            const optionsHtml = (cfg.cards || []).map((c) =>
                `<option value="${escapeXml(c.card_id)}">${escapeXml(c.roman ? c.roman + ' · ' : '')}${escapeXml(c.name_en)} — ${escapeXml(c.name_vi)}</option>`
            ).join('');

            manualRowsWrap.innerHTML = (sp.positions || []).map((pos, idx) => `
                <div class="mayhen_tarot_manual_row">
                    <label class="mayhen_tarot_label">${idx + 1}. ${escapeXml(pos.label || pos.name || '')}</label>
                    <select class="mayhen_tarot_select" data-manual-card="${idx}">
                        <option value="">-- Chọn lá bài đã rút từ bộ bài thật --</option>
                        ${optionsHtml}
                    </select>
                    <select class="mayhen_tarot_select" data-manual-orient="${idx}">
                        <option value="upright">Chiều Xuôi (Upright)</option>
                        <option value="reversed">Chiều Ngược (Reversed)</option>
                    </select>
                </div>
            `).join('');
        }

        if (manualSpreadSel) {
            manualSpreadSel.addEventListener('change', renderManualSlots);
        }

        if (manualSubmitBtn) {
            manualSubmitBtn.addEventListener('click', async () => {
                hideAlert();
                const spId = manualSpreadSel?.value || state.spreadId;
                const cardSels = Array.from(manualRowsWrap.querySelectorAll('[data-manual-card]'));
                const orientSels = Array.from(manualRowsWrap.querySelectorAll('[data-manual-orient]'));
                const cards = [];
                const seen = new Set();
                for (let i = 0; i < cardSels.length; i++) {
                    const cid = cardSels[i].value;
                    if (!cid) {
                        showAlert(`Vui lòng chọn lá bài cho vị trí số ${i + 1}.`);
                        return;
                    }
                    if (seen.has(cid)) {
                        showAlert('Trong cùng một bộ bài Tarot vật lý, mỗi lá chỉ xuất hiện tối đa 1 lần. Vui lòng kiểm tra lại lá bị trùng.');
                        return;
                    }
                    seen.add(cid);
                    cards.push({ card_id: cid, is_reversed: orientSels[i].value === 'reversed' });
                }

                manualSubmitBtn.disabled = true;
                AnalysisOverlay.show('Đang Luận Giải Bộ Bài Vật Lý', []);
                try {
                    const res = await fetch(cfg.endpoints.manual, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        body: JSON.stringify({
                            spread_id: spId,
                            category: document.getElementById('tarotManualCategory')?.value || 'general',
                            question: document.getElementById('tarotManualQuestion')?.value.trim() || null,
                            cards,
                        }),
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        AnalysisOverlay.hide();
                        showAlert(data.message || 'Không thể lưu trải bài vật lý.');
                        return;
                    }
                    window.location.href = data.redirect_url || data.result_url;
                } catch (err) {
                    AnalysisOverlay.hide();
                    showAlert('Kết nối tạm gián đoạn khi gửi bài vật lý.');
                } finally {
                    manualSubmitBtn.disabled = false;
                }
            });
        }
    }

    /* ----------------------------------------------------------------------
       4. Reading Result Page Controller (/tarot/ket-qua/{slug})
       ---------------------------------------------------------------------- */
    function initReadingResultPage() {
        const resultCfgEl = document.getElementById('mayhenTarotResultConfig');
        if (!resultCfgEl) return;
        const cfg = JSON.parse(resultCfgEl.textContent || '{}');

        // 3 Depth tabs (quick / standard / deep)
        const depthTabs = document.querySelectorAll('[data-tarot-depth-tab]');
        const depthPanels = document.querySelectorAll('[data-tarot-depth-panel]');
        depthTabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const target = tab.getAttribute('data-tarot-depth-tab');
                depthTabs.forEach((t) => t.classList.toggle('is-active', t === tab));
                depthPanels.forEach((p) => p.classList.toggle('is-active', p.getAttribute('data-tarot-depth-panel') === target));
            });
        });

        // Click card on board -> jump to detail & switch to standard depth
        document.querySelectorAll('[data-tarot-focus-card]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const pos = btn.getAttribute('data-tarot-focus-card');
                const stdTab = document.querySelector('[data-tarot-depth-tab="standard"]');
                if (stdTab) stdTab.click();
                document.querySelectorAll('.mayhen_tarot_card_detail_row').forEach((row) => {
                    const match = row.getAttribute('data-card-pos') === pos;
                    row.classList.toggle('is-focused', match);
                    if (match) row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            });
        });

        // Follow-up AI Q&A
        const fuForm = document.getElementById('tarotFollowupForm');
        const fuInput = document.getElementById('tarotFollowupInput');
        const fuList = document.getElementById('tarotFollowupList');
        const fuStatus = document.getElementById('tarotFollowupStatus');
        if (fuForm && fuInput && fuList) {
            fuForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const q = fuInput.value.trim();
                if (q.length < 4) return;
                const submitBtn = fuForm.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;
                if (fuStatus) fuStatus.textContent = 'Đang phân tích sâu dựa trên các lá bài đã rút...';
                try {
                    const res = await fetch(cfg.endpoints.followup, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        body: JSON.stringify({ question: q }),
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        if (fuStatus) fuStatus.textContent = data.message || 'Không thể gửi câu hỏi lúc này.';
                    } else {
                        const item = document.createElement('div');
                        item.className = 'mayhen_tarot_qa_item';
                        item.innerHTML = `<div class="mayhen_tarot_qa_q">Bạn hỏi: ${escapeXml(data.followup.question)}</div><div class="mayhen_tarot_qa_a">${escapeXml(data.followup.answer)}</div>`;
                        fuList.appendChild(item);
                        fuInput.value = '';
                        if (fuStatus) fuStatus.textContent = 'Đã cập nhật góc nhìn bổ sung.';
                    }
                } catch (err) {
                    if (fuStatus) fuStatus.textContent = 'Kết nối tạm gián đoạn.';
                } finally {
                    if (submitBtn) submitBtn.disabled = false;
                }
            });
        }

        // Journal Save
        const jnForm = document.getElementById('tarotJournalForm');
        const jnStatus = document.getElementById('tarotJournalStatus');
        const jnList = document.getElementById('tarotJournalList');
        if (jnForm) {
            jnForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const payload = {
                    feeling_note: jnForm.querySelector('[name="feeling_note"]')?.value.trim() || null,
                    realization_note: jnForm.querySelector('[name="realization_note"]')?.value.trim() || null,
                    action_note: jnForm.querySelector('[name="action_note"]')?.value.trim() || null,
                    outcome_note: jnForm.querySelector('[name="outcome_note"]')?.value.trim() || null,
                };
                if (jnStatus) jnStatus.textContent = 'Đang lưu vào Sổ Bài Tarot...';
                try {
                    const res = await fetch(cfg.endpoints.journal, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        if (jnStatus) jnStatus.textContent = 'Đã lưu ghi chú chiêm nghiệm.';
                        if (jnList) {
                            const div = document.createElement('div');
                            div.className = 'mayhen_tarot_qa_item';
                            div.innerHTML = `<div class="mayhen_tarot_qa_q">${escapeXml(data.journal.created_at || 'Vừa xong')}</div><div class="mayhen_tarot_qa_a">${escapeXml([payload.feeling_note, payload.realization_note, payload.action_note, payload.outcome_note].filter(Boolean).join(' · '))}</div>`;
                            jnList.prepend(div);
                        }
                        jnForm.reset();
                    } else {
                        if (jnStatus) jnStatus.textContent = data.message || 'Không thể lưu nhật ký.';
                    }
                } catch (err) {
                    if (jnStatus) jnStatus.textContent = 'Kết nối tạm gián đoạn.';
                }
            });
        }

        // Anonymous Share Toggle
        const shareBtn = document.getElementById('tarotShareToggleBtn');
        const revokeBtn = document.getElementById('tarotShareRevokeBtn');
        const shareLinkBox = document.getElementById('tarotShareUrlBox');
        async function toggleShare(action) {
            try {
                const res = await fetch(cfg.endpoints.toggle_share, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: JSON.stringify({ action }),
                });
                const data = await res.json();
                if (res.ok && data.success && shareLinkBox) {
                    if (data.share_url) {
                        shareLinkBox.innerHTML = `Liên kết ẩn danh (đã ẩn câu hỏi riêng tư): <a href="${escapeXml(data.share_url)}" target="_blank" rel="noopener">${escapeXml(data.share_url)}</a>`;
                        if (revokeBtn) revokeBtn.style.display = 'inline-flex';
                    } else {
                        shareLinkBox.textContent = 'Đã thu hồi liên kết chia sẻ. Trải bài đang ở chế độ riêng tư tuyệt đối.';
                        if (revokeBtn) revokeBtn.style.display = 'none';
                    }
                }
            } catch (e) {}
        }
        if (shareBtn) shareBtn.addEventListener('click', () => toggleShare('enable'));
        if (revokeBtn) revokeBtn.addEventListener('click', () => toggleShare('revoke'));

        // Reinterpret AI
        const reintBtn = document.getElementById('tarotReinterpretBtn');
        if (reintBtn) {
            reintBtn.addEventListener('click', async () => {
                reintBtn.disabled = true;
                AnalysisOverlay.show('Đang Luận Giải Lại Bằng AI 6 Tầng', []);
                try {
                    const res = await fetch(cfg.endpoints.reinterpret, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        body: JSON.stringify({}),
                    });
                    if (res.ok) {
                        window.location.reload();
                    } else {
                        AnalysisOverlay.hide();
                        reintBtn.disabled = false;
                    }
                } catch (e) {
                    AnalysisOverlay.hide();
                    reintBtn.disabled = false;
                }
            });
        }

        // Feedback rating buttons
        document.querySelectorAll('[data-tarot-feedback]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const rating = btn.getAttribute('data-tarot-feedback');
                await fetch(cfg.endpoints.feedback, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: JSON.stringify({ feedback_rating: rating }),
                });
                document.querySelectorAll('[data-tarot-feedback]').forEach((b) => b.classList.remove('is-active'));
                btn.classList.add('is-active');
            });
        });

        // HiDPI PNG Art Card Export (1:1, 4:5, 9:16) — Vietnamese-safe fonts (NEVER Georgia)
        document.querySelectorAll('[data-tarot-export-ratio]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const ratio = btn.getAttribute('data-tarot-export-ratio') || '4:5';
                exportTarotArtCardPng(cfg.exportData || {}, ratio);
            });
        });
    }

    async function exportTarotArtCardPng(data, ratio) {
        if (document.fonts && document.fonts.ready) {
            try { await document.fonts.ready; } catch (e) {}
        }

        const dims = {
            '1:1': [1080, 1080],
            '4:5': [1080, 1350],
            '9:16': [1080, 1920],
        }[ratio] || [1080, 1350];

        const [W, H] = dims;
        const canvas = document.createElement('canvas');
        canvas.width = W;
        canvas.height = H;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        const SERIF = '"MayhenV3EditorialSerif", "Times New Roman", serif';
        const SANS = '"Segoe UI", Arial, sans-serif';

        // Midnight Indigo Velvet & Champagne Gold Fine Art Canvas
        const bgGrad = ctx.createLinearGradient(0, 0, W, H);
        bgGrad.addColorStop(0, '#0D1322');
        bgGrad.addColorStop(0.55, '#141C31');
        bgGrad.addColorStop(1, '#1E2A47');
        ctx.fillStyle = bgGrad;
        ctx.fillRect(0, 0, W, H);

        // Double Gold Hairline Frame
        ctx.strokeStyle = 'rgba(200, 160, 85, 0.72)';
        ctx.lineWidth = 3;
        ctx.strokeRect(42, 42, W - 84, H - 84);
        ctx.strokeStyle = 'rgba(231, 210, 163, 0.28)';
        ctx.lineWidth = 1.2;
        ctx.strokeRect(58, 58, W - 116, H - 116);

        // Header Brand
        ctx.textAlign = 'center';
        ctx.fillStyle = '#E7D2A3';
        ctx.font = `600 22px ${SANS}`;
        ctx.fillText('MÂY HẸN · THE ART OF TAROT', W / 2, 128);

        ctx.fillStyle = '#FFFDF8';
        ctx.font = `600 44px ${SERIF}`;
        ctx.fillText(String(data.spreadName || 'Trải Bài Tarot').slice(0, 44), W / 2, 190);

        ctx.fillStyle = 'rgba(250, 245, 235, 0.76)';
        ctx.font = `400 22px ${SANS}`;
        ctx.fillText(`Rider–Waite–Smith · ${data.dateLabel || ''}`, W / 2, 232);

        // Preload real RWS card images for Canvas HiDPI Export
        const loadImg = (src) => new Promise((resolve) => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => resolve(img);
            img.onerror = () => resolve(null);
            img.src = src;
        });

        const cards = (data.cards || []).slice(0, 5);
        const loadedImgs = await Promise.all(
            cards.map((c) => (c.slug ? loadImg(`/assets/client/images/tarot/rws/${encodeURIComponent(c.slug)}.jpg`) : Promise.resolve(null)))
        );

        const panelY = 270;
        const cardW = cards.length <= 3 ? 236 : 168;
        const cardH = Math.round(cardW * 1.71);
        const gap = 26;
        const totalW = cards.length * cardW + Math.max(0, cards.length - 1) * gap;
        let startX = (W - totalW) / 2;

        cards.forEach((c, idx) => {
            const img = loadedImgs[idx];
            const isRev = c.orientation === 'reversed' || c.is_reversed === true;
            ctx.save();
            ctx.fillStyle = '#FAF5EB';
            ctx.fillRect(startX, panelY, cardW, cardH);
            if (img) {
                ctx.translate(startX + cardW / 2, panelY + cardH / 2);
                if (isRev) ctx.rotate(Math.PI);
                ctx.drawImage(img, -cardW / 2 + 4, -cardH / 2 + 4, cardW - 8, cardH - 8);
            }
            ctx.restore();

            ctx.strokeStyle = '#C8A055';
            ctx.lineWidth = 2.4;
            ctx.strokeRect(startX, panelY, cardW, cardH);

            ctx.fillStyle = '#FFFDF8';
            ctx.font = `600 ${cards.length <= 3 ? 21 : 16}px ${SERIF}`;
            ctx.fillText(String(c.name_en || ''), startX + cardW / 2, panelY + cardH + 30);

            ctx.fillStyle = isRev ? '#F4A6B4' : '#E7D2A3';
            ctx.font = `700 14px ${SANS}`;
            ctx.fillText(isRev ? 'LÁ NGƯỢC' : 'LÁ XUÔI', startX + cardW / 2, panelY + cardH + 54);

            startX += cardW + gap;
        });

        // Core Insight Box
        const insightY = panelY + cardH + 86;
        ctx.fillStyle = 'rgba(250, 245, 235, 0.08)';
        ctx.fillRect(88, insightY, W - 176, H - insightY - 140);
        ctx.strokeStyle = 'rgba(200, 160, 85, 0.35)';
        ctx.lineWidth = 1.5;
        ctx.strokeRect(88, insightY, W - 176, H - insightY - 140);

        ctx.fillStyle = '#E7D2A3';
        ctx.font = `600 20px ${SANS}`;
        ctx.fillText('THÔNG ĐIỆP CỐT LÕI (ẨN DANH)', W / 2, insightY + 46);

        ctx.fillStyle = '#FFFDF8';
        ctx.font = `400 27px ${SERIF}`;
        wrapCanvasText(ctx, String(data.summary || ''), W / 2, insightY + 98, W - 240, 40, 6);

        // Footer
        ctx.fillStyle = 'rgba(231, 210, 163, 0.78)';
        ctx.font = `400 20px ${SANS}`;
        ctx.fillText('mayhen.vn/tarot — Soi chiếu tâm trí · Vun đắp nhân duyên', W / 2, H - 82);

        const link = document.createElement('a');
        link.download = `mayhen-tarot-${ratio.replace(':', 'x')}-${Date.now()}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    }

    function wrapCanvasText(ctx, text, x, y, maxWidth, lineHeight, maxLines = 4) {
        const words = String(text || '').split(/\s+/);
        let line = '';
        let lineCount = 0;
        for (let n = 0; n < words.length; n++) {
            const testLine = line ? `${line} ${words[n]}` : words[n];
            if (ctx.measureText(testLine).width > maxWidth && line) {
                lineCount++;
                if (lineCount >= maxLines) {
                    ctx.fillText(line + '…', x, y);
                    return;
                }
                ctx.fillText(line, x, y);
                line = words[n];
                y += lineHeight;
            } else {
                line = testLine;
            }
        }
        if (line) ctx.fillText(line, x, y);
    }

    /* ----------------------------------------------------------------------
       5. 78-Card Encyclopedia Search & Filter (/tarot/78-la-bai)
       ---------------------------------------------------------------------- */
    function stripVietnameseTones(str) {
        return String(str || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/Đ/g, 'D')
            .toLowerCase();
    }

    function initLibraryPage() {
        const searchInput = document.getElementById('tarotLibrarySearch');
        const filterBtns = document.querySelectorAll('[data-tarot-suit-filter]');
        const cards = document.querySelectorAll('[data-tarot-library-item]');
        if (!cards.length) return;

        let activeSuit = 'all';
        function applyFilter() {
            const q = stripVietnameseTones(searchInput?.value || '').trim();
            let visibleCount = 0;
            cards.forEach((card) => {
                const suit = card.getAttribute('data-suit') || '';
                const haystack = stripVietnameseTones(card.getAttribute('data-search') || '');
                const okSuit = activeSuit === 'all' || suit === activeSuit;
                const okSearch = !q || haystack.includes(q);
                const show = okSuit && okSearch;
                card.classList.toggle('is-hidden', !show);
                if (show) visibleCount++;
            });
            const countEl = document.getElementById('tarotLibraryCount');
            if (countEl) countEl.textContent = `${visibleCount} / 78 lá bài`;
        }

        filterBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                activeSuit = btn.getAttribute('data-tarot-suit-filter') || 'all';
                filterBtns.forEach((b) => b.classList.toggle('is-active', b === btn));
                applyFilter();
            });
        });
        if (searchInput) searchInput.addEventListener('input', applyFilter);
    }

    /* ----------------------------------------------------------------------
       6. Bootstrap
       ---------------------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', () => {
        hydrateCardArtworks();
        initLandingReadingTable();
        initReadingResultPage();
        initLibraryPage();
    });
})();
