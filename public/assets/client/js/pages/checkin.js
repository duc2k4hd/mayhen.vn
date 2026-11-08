/**
 * CỔNG TIẾP TÂN ĐIỂM DANH & QUÉT MÃ QR (CHECK-IN & TABLE FINDER)
 * Mây Hẹn (mayhen.vn) — Pure Native ES Module
 * Zero External Libraries — Uses Native Web APIs & AudioContext
 */

document.addEventListener('DOMContentLoaded', () => {
    const appEl = document.getElementById('mayhen_checkin_app');
    if (!appEl) return;

    const slug = appEl.getAttribute('data-slug');
    const scanUrl = appEl.getAttribute('data-scan-url');
    const searchUrl = appEl.getAttribute('data-search-url');
    const undoUrl = appEl.getAttribute('data-undo-url');
    const updateTableUrl = appEl.getAttribute('data-update-table-url');

    // DOM Elements - Tabs
    const tabBtns = document.querySelectorAll('.mayhen_checkin_tab');
    const panelScanner = document.getElementById('panel_scanner');
    const panelSearch = document.getElementById('panel_search');

    // DOM Elements - Camera Scanner
    const video = document.getElementById('checkin_video');
    const btnToggleCamera = document.getElementById('btn_toggle_camera');
    const btnManualTokenToggle = document.getElementById('btn_manual_token_toggle');
    const manualTokenForm = document.getElementById('manual_token_form');
    const formTokenManual = document.getElementById('form_token_manual');
    const inputGuestToken = document.getElementById('input_guest_token');

    // DOM Elements - Search
    const inputQuickSearch = document.getElementById('input_quick_search');
    const searchResultsContainer = document.getElementById('search_results_container');

    // DOM Elements - Modal
    const modalBackdrop = document.getElementById('checkin_modal');
    const btnModalClose = document.getElementById('btn_modal_close');
    const btnModalNext = document.getElementById('btn_modal_next');
    const modalSalutation = document.getElementById('modal_guest_salutation');
    const modalName = document.getElementById('modal_guest_name');
    const modalGroup = document.getElementById('modal_guest_group');
    const modalTableName = document.getElementById('modal_table_name');
    const modalGuestCount = document.getElementById('modal_guest_count');
    const modalDietaryWrap = document.getElementById('modal_dietary_wrap');
    const modalDietary = document.getElementById('modal_dietary');
    const modalAlreadyNotice = document.getElementById('modal_already_checked_notice');
    const modalPrevTime = document.getElementById('modal_prev_time');
    const modalStatusTag = document.getElementById('modal_status_tag');

    // Quick Table Edit inside Modal
    const btnEditTable = document.getElementById('btn_edit_table');
    const quickTableBox = document.getElementById('quick_table_box');
    const inputModalNewTable = document.getElementById('input_modal_new_table');
    const btnSaveModalTable = document.getElementById('btn_save_modal_table');

    // Recent Feed & Counter
    const recentList = document.getElementById('recent_checkin_list');
    const emptyRecentNotice = document.getElementById('empty_recent_notice');
    const counterCheckedIn = document.getElementById('checked_in_counter');
    const progressCheckedIn = document.getElementById('checked_in_progress');
    const counterGuests = document.getElementById('checked_in_guests_counter');

    let activeGuestId = null;
    let cameraStream = null;
    let isCameraActive = false;
    let currentFacingMode = 'environment';
    let isScanCooldown = false;
    let barcodeDetector = null;
    let detectorTimer = null;

    // Check BarcodeDetector support
    if ('BarcodeDetector' in window) {
        try {
            barcodeDetector = new window.BarcodeDetector({ formats: ['qr_code'] });
        } catch (e) {
            barcodeDetector = null;
        }
    }

    /* --------------------------------------------------------------------------
       1. Native Web Audio Ding Synthesizer (Zero External Audio File)
       -------------------------------------------------------------------------- */
    function playSuccessChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const now = ctx.currentTime;

            // Two-tone Quiet Luxury chime (E5 -> G#5)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, now); // E5
            gain1.gain.setValueAtTime(0.18, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.5);

            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(830.61, now + 0.12); // G#5
            gain2.gain.setValueAtTime(0.22, now + 0.12);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.8);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.12);
            osc2.stop(now + 0.8);
        } catch (err) {
            // Audio context not allowed or blocked, silently pass
        }
    }

    /* --------------------------------------------------------------------------
       2. Tabs Switching
       -------------------------------------------------------------------------- */
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-tab');
            tabBtns.forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');

            if (target === 'scanner') {
                panelScanner.classList.add('is-active');
                panelSearch.classList.remove('is-active');
                startCamera();
            } else {
                panelScanner.classList.remove('is-active');
                panelSearch.classList.add('is-active');
                stopCamera();
                if (inputQuickSearch) inputQuickSearch.focus();
            }
        });
    });

    /* --------------------------------------------------------------------------
       3. Camera Streaming & Barcode Scanning
       -------------------------------------------------------------------------- */
    async function startCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            btnToggleCamera.textContent = 'Trình duyệt không hỗ trợ camera';
            btnToggleCamera.disabled = true;
            manualTokenForm.style.display = 'block';
            return;
        }

        try {
            stopCamera();
            const constraints = {
                video: {
                    facingMode: currentFacingMode,
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                },
                audio: false,
            };

            cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = cameraStream;
            await video.play();
            isCameraActive = true;
            btnToggleCamera.textContent = '🔄 Đổi Camera Trước/Sau';

            startScanLoop();
        } catch (err) {
            btnToggleCamera.textContent = '⚠️ Không thể mở camera (Nhập mã)';
            manualTokenForm.style.display = 'block';
        }
    }

    function stopCamera() {
        if (detectorTimer) {
            clearInterval(detectorTimer);
            detectorTimer = null;
        }
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
        if (video) video.srcObject = null;
        isCameraActive = false;
    }

    function startScanLoop() {
        if (!barcodeDetector) return;

        detectorTimer = setInterval(async () => {
            if (!isCameraActive || isScanCooldown || video.readyState !== video.HAVE_ENOUGH_DATA) {
                return;
            }

            try {
                const barcodes = await barcodeDetector.detect(video);
                if (barcodes.length > 0) {
                    const rawValue = barcodes[0].rawValue;
                    if (rawValue) {
                        handleScannedToken(rawValue);
                    }
                }
            } catch (e) {
                // Ignore detection frame errors
            }
        }, 350);
    }

    btnToggleCamera.addEventListener('click', () => {
        currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
        startCamera();
    });

    btnManualTokenToggle.addEventListener('click', () => {
        manualTokenForm.style.display = manualTokenForm.style.display === 'none' ? 'block' : 'none';
        if (manualTokenForm.style.display === 'block') {
            inputGuestToken.focus();
        }
    });

    formTokenManual.addEventListener('submit', (e) => {
        e.preventDefault();
        const val = inputGuestToken.value.trim();
        if (val) {
            handleScannedToken(val);
            inputGuestToken.value = '';
        }
    });

    // Auto start camera if scanner tab active
    if (panelScanner.classList.contains('is-active')) {
        startCamera();
    }

    /* --------------------------------------------------------------------------
       4. Process Check-in AJAX
       -------------------------------------------------------------------------- */
    async function handleScannedToken(tokenOrUrl, guestId = null) {
        if (isScanCooldown) return;
        isScanCooldown = true;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const payload = guestId ? { guest_id: guestId } : { token: tokenOrUrl };

        try {
            const res = await fetch(scanUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                alert(data.message || 'Không tìm thấy thông tin khách mời.');
                setTimeout(() => { isScanCooldown = false; }, 1500);
                return;
            }

            playSuccessChime();
            showSuccessModal(data);
            updateCounters(data.stats);
            prependRecentItem(data.guest);

        } catch (err) {
            alert('Lỗi kết nối máy chủ khi điểm danh.');
        } finally {
            // 3s cooldown before scanning next QR code
            setTimeout(() => {
                isScanCooldown = false;
            }, 3000);
        }
    }

    /* --------------------------------------------------------------------------
       5. Success Modal Presentation
       -------------------------------------------------------------------------- */
    function showSuccessModal(data) {
        const guest = data.guest;
        activeGuestId = guest.id;

        modalSalutation.textContent = guest.salutation ? `Trân trọng kính đón ${guest.salutation}` : 'Trân trọng kính đón';
        modalName.textContent = guest.name;
        modalGroup.textContent = `Nhóm: ${guest.group_name}`;
        modalTableName.textContent = guest.table_name || 'Chưa xếp bàn';
        modalGuestCount.textContent = `${guest.checked_in_attendees || guest.expected_attendees} khách`;

        if (guest.dietary) {
            modalDietary.textContent = guest.dietary;
            modalDietaryWrap.style.display = 'block';
        } else {
            modalDietaryWrap.style.display = 'none';
        }

        if (data.already_checked_in) {
            modalAlreadyNotice.style.display = 'block';
            modalPrevTime.textContent = data.previous_time || '';
            modalStatusTag.textContent = '⚠️ ĐÃ ĐIỂM DANH TRƯỚC ĐÓ';
            modalStatusTag.style.background = '#FFF3E0';
            modalStatusTag.style.color = '#E65100';
        } else {
            modalAlreadyNotice.style.display = 'none';
            modalStatusTag.textContent = '✓ ĐÃ ĐÓN KHÁCH VÀO BÀN';
            modalStatusTag.style.background = '#E8F5E9';
            modalStatusTag.style.color = '#2E7D32';
        }

        quickTableBox.style.display = 'none';
        modalBackdrop.style.display = 'flex';
    }

    function closeModal() {
        modalBackdrop.style.display = 'none';
        activeGuestId = null;
    }

    btnModalClose.addEventListener('click', closeModal);
    btnModalNext.addEventListener('click', closeModal);
    modalBackdrop.addEventListener('click', (e) => {
        if (e.target === modalBackdrop) closeModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modalBackdrop.style.display !== 'none') {
            closeModal();
        }
    });

    /* Quick Table Edit within Modal */
    btnEditTable.addEventListener('click', () => {
        quickTableBox.style.display = quickTableBox.style.display === 'none' ? 'block' : 'none';
        if (quickTableBox.style.display === 'block') {
            inputModalNewTable.value = modalTableName.textContent === 'Chưa xếp bàn' ? '' : modalTableName.textContent;
            inputModalNewTable.focus();
        }
    });

    btnSaveModalTable.addEventListener('click', async () => {
        const newTable = inputModalNewTable.value.trim();
        if (!newTable || !activeGuestId) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const res = await fetch(`${updateTableUrl}/${activeGuestId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({ table_name: newTable }),
            });
            const d = await res.json();
            if (d.success) {
                modalTableName.textContent = d.table_name;
                quickTableBox.style.display = 'none';
                // update recent row if exists
                const row = document.getElementById(`recent_guest_${activeGuestId}`);
                if (row) {
                    const meta = row.querySelector('.mayhen_checkin_recent_meta strong');
                    if (meta) meta.textContent = d.table_name;
                }
            }
        } catch (err) {
            alert('Lỗi cập nhật bàn tiệc');
        }
    });

    /* --------------------------------------------------------------------------
       6. Live Counters & Recent Feed Updater
       -------------------------------------------------------------------------- */
    function updateCounters(stats) {
        if (!stats) return;
        if (counterCheckedIn) {
            counterCheckedIn.innerHTML = `${stats.checked_in_count} <span class="mayhen_checkin_counter_total">/ ${stats.total_expected} khách</span>`;
        }
        if (counterGuests) {
            counterGuests.textContent = stats.checked_in_guests;
        }
        if (progressCheckedIn && stats.total_expected > 0) {
            const pct = Math.min(100, Math.round((stats.checked_in_count / stats.total_expected) * 100));
            progressCheckedIn.style.width = `${pct}%`;
        }
    }

    function prependRecentItem(guest) {
        if (!recentList) return;
        if (emptyRecentNotice) emptyRecentNotice.style.display = 'none';

        const existing = document.getElementById(`recent_guest_${guest.id}`);
        if (existing) existing.remove();

        const item = document.createElement('div');
        item.className = 'mayhen_checkin_recent_item';
        item.id = `recent_guest_${guest.id}`;
        item.innerHTML = `
            <div class="mayhen_checkin_recent_main">
                <span class="mayhen_checkin_recent_name">
                    ${guest.salutation ? guest.salutation + ' ' : ''}${guest.name}
                </span>
                <span class="mayhen_checkin_recent_meta">
                    Bàn: <strong>${guest.table_name || 'Chưa xếp'}</strong> • ${guest.checked_in_attendees || guest.expected_attendees} người
                </span>
            </div>
            <div class="mayhen_checkin_recent_action">
                <span class="mayhen_checkin_recent_time">Vừa xong</span>
                <button type="button" class="mayhen_checkin_btn_undo" data-guest-id="${guest.id}" title="Hủy điểm danh nếu nhầm">
                    Hủy ✕
                </button>
            </div>
        `;
        recentList.prepend(item);
    }

    /* Undo Check-in Delegation */
    if (recentList) {
        recentList.addEventListener('click', async (e) => {
            const btn = e.target.closest('.mayhen_checkin_btn_undo');
            if (!btn) return;

            const guestId = btn.getAttribute('data-guest-id');
            if (!confirm('Bạn có chắc chắn muốn hủy điểm danh cho khách này?')) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            try {
                const res = await fetch(`${undoUrl}/${guestId}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                    },
                });
                const d = await res.json();
                if (d.success) {
                    const row = document.getElementById(`recent_guest_${guestId}`);
                    if (row) row.remove();
                }
            } catch (err) {
                alert('Lỗi hủy điểm danh');
            }
        });
    }

    /* --------------------------------------------------------------------------
       7. Fast Live Search
       -------------------------------------------------------------------------- */
    let searchDebounce = null;
    if (inputQuickSearch) {
        inputQuickSearch.addEventListener('input', () => {
            clearTimeout(searchDebounce);
            const q = inputQuickSearch.value.trim();
            if (!q) {
                searchResultsContainer.innerHTML = '<div class="mayhen_checkin_empty_state">Nhập tên khách mời để tra cứu nhanh vị trí bàn tiệc và điểm danh.</div>';
                return;
            }

            searchDebounce = setTimeout(async () => {
                try {
                    const res = await fetch(`${searchUrl}?q=${encodeURIComponent(q)}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const d = await res.json();
                    renderSearchResults(d.guests || []);
                } catch (err) {
                    // search error
                }
            }, 250);
        });
    }

    function renderSearchResults(guests) {
        if (!guests || guests.length === 0) {
            searchResultsContainer.innerHTML = '<div class="mayhen_checkin_empty_state">Không tìm thấy khách mời nào phù hợp.</div>';
            return;
        }

        searchResultsContainer.innerHTML = '';
        guests.forEach(g => {
            const item = document.createElement('div');
            item.className = 'mayhen_checkin_search_item';
            item.innerHTML = `
                <div class="search_item_info">
                    <div class="search_item_name">${g.salutation ? g.salutation + ' ' : ''}${g.name}</div>
                    <div class="search_item_meta">
                        Bàn: <strong>${g.table_name}</strong> • ${g.group_name || 'Khách'} • ${g.expected_attendees} khách
                        ${g.is_checked_in ? `• <span style="color: #2E7D32;">✓ Đã tới (${g.checked_in_time})</span>` : ''}
                    </div>
                </div>
                <button type="button" class="mayhen_checkin_btn ${g.is_checked_in ? 'mayhen_checkin_btn--secondary' : 'mayhen_checkin_btn--primary'} btn_quick_checkin" data-guest-id="${g.id}">
                    ${g.is_checked_in ? 'Xem lại' : 'Điểm danh ✓'}
                </button>
            `;
            searchResultsContainer.appendChild(item);
        });
    }

    if (searchResultsContainer) {
        searchResultsContainer.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn_quick_checkin');
            if (!btn) return;
            const guestId = btn.getAttribute('data-guest-id');
            if (guestId) {
                handleScannedToken(null, guestId);
            }
        });
    }

    // 7. Hỗ trợ nút quét nhanh trong Chế Độ Diễn Tập Lễ Tân (TASK-VAL-20)
    document.querySelectorAll('.btn-dry-run-scan').forEach(btn => {
        btn.addEventListener('click', () => {
            const token = btn.getAttribute('data-token');
            if (token) {
                handleScannedToken(token);
            }
        });
    });

    // 8. Bàn Xử Lý Ngoại Lệ Đón Khách (RSVP Exception Desk - TASK-VAL-21)
    const btnOpenException = document.getElementById('btn_open_exception_modal');
    const modalException = document.getElementById('modal_exception_desk');
    const btnCloseException = document.getElementById('btn_close_exception_modal');
    const formWalkin = document.getElementById('form_walkin_guest');
    const walkinMsgBox = document.getElementById('walkin_msg_box');
    const walkinUrl = appEl.getAttribute('data-walkin-url');

    if (btnOpenException && modalException) {
        btnOpenException.addEventListener('click', () => {
            modalException.style.display = 'flex';
        });

        if (btnCloseException) {
            btnCloseException.addEventListener('click', () => {
                modalException.style.display = 'none';
            });
        }

        modalException.addEventListener('click', (e) => {
            if (e.target === modalException) {
                modalException.style.display = 'none';
            }
        });

        if (formWalkin && walkinUrl) {
            formWalkin.addEventListener('submit', async (e) => {
                e.preventDefault();
                const submitBtn = document.getElementById('btn_submit_walkin');
                if (submitBtn) submitBtn.disabled = true;

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const payload = {
                    name: document.getElementById('walkin_name')?.value,
                    salutation: document.getElementById('walkin_salutation')?.value,
                    attendees: document.getElementById('walkin_attendees')?.value,
                    table_name: document.getElementById('walkin_table')?.value,
                    group_name: document.getElementById('walkin_group')?.value,
                    note: document.getElementById('walkin_note')?.value,
                };

                try {
                    const res = await fetch(walkinUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload)
                    });

                    const json = await res.json();
                    if (!json.success) throw new Error(json.message || 'Lỗi xử lý khách phát sinh.');

                    if (walkinMsgBox) {
                        walkinMsgBox.style.display = 'block';
                        walkinMsgBox.style.background = '#E8F5E9';
                        walkinMsgBox.style.color = '#2E7D32';
                        walkinMsgBox.innerText = json.message || 'Đón tiếp thành công!';
                    }

                    setTimeout(() => {
                        modalException.style.display = 'none';
                        formWalkin.reset();
                        if (walkinMsgBox) walkinMsgBox.style.display = 'none';
                        window.location.reload();
                    }, 1200);
                } catch (err) {
                    if (walkinMsgBox) {
                        walkinMsgBox.style.display = 'block';
                        walkinMsgBox.style.background = '#FFEBEE';
                        walkinMsgBox.style.color = '#C62828';
                        walkinMsgBox.innerText = err.message || 'Đã có lỗi xảy ra.';
                    }
                } finally {
                    if (submitBtn) submitBtn.disabled = false;
                }
            });
        }
    }

    /* --------------------------------------------------------------------------
       9. TASK-VAL-23: Bàn Giao Ca Trực Lễ Tân (Staff Shift Handover)
       -------------------------------------------------------------------------- */
    const btnOpenHandover = document.getElementById('btn_open_handover_modal');
    const modalHandover = document.getElementById('modal_shift_handover');
    const btnCloseHandover = document.getElementById('btn_close_handover_modal');
    const formHandover = document.getElementById('form_submit_handover');
    const hoMsgBox = document.getElementById('ho_msg_box');
    const handoverSummaryUrl = appEl.getAttribute('data-handover-summary-url');
    const handoverSubmitUrl = appEl.getAttribute('data-handover-submit-url');

    if (btnOpenHandover && modalHandover) {
        btnOpenHandover.addEventListener('click', async () => {
            modalHandover.style.display = 'flex';
            const loadingEl = document.getElementById('handover_summary_loading');
            const contentEl = document.getElementById('handover_summary_content');
            if (loadingEl) loadingEl.style.display = 'block';
            if (contentEl) contentEl.style.display = 'none';

            if (handoverSummaryUrl) {
                try {
                    const res = await fetch(handoverSummaryUrl, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const d = await res.json();
                    if (d.success && d.summary) {
                        const sum = d.summary.summary;
                        const shift = d.summary.shift;
                        document.getElementById('ho_stat_checked_in').innerText = sum.total_checked_in || 0;
                        document.getElementById('ho_stat_attendees').innerText = sum.total_attendees || 0;
                        document.getElementById('ho_stat_walkin').innerText = sum.walk_in_count || 0;
                        document.getElementById('ho_stat_uncheckin').innerText = sum.remaining_uncheckin_guests || 0;
                        document.getElementById('ho_from_staff').value = shift.staff_name ? `${shift.name} - ${shift.staff_name}` : 'Lễ Tân Hiện Tại';

                        if (loadingEl) loadingEl.style.display = 'none';
                        if (contentEl) contentEl.style.display = 'block';
                    }
                } catch (e) {
                    if (loadingEl) loadingEl.innerText = 'Lỗi tải tóm tắt ca trực. Vui lòng thử lại.';
                }
            }
        });

        if (btnCloseHandover) {
            btnCloseHandover.addEventListener('click', () => {
                modalHandover.style.display = 'none';
            });
        }

        modalHandover.addEventListener('click', (e) => {
            if (e.target === modalHandover) {
                modalHandover.style.display = 'none';
            }
        });

        if (formHandover && handoverSubmitUrl) {
            formHandover.addEventListener('submit', async (e) => {
                e.preventDefault();
                const submitBtn = document.getElementById('btn_submit_handover');
                if (submitBtn) submitBtn.disabled = true;

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const payload = {
                    handed_to_staff: document.getElementById('ho_to_staff')?.value,
                    notes: document.getElementById('ho_notes')?.value,
                };

                try {
                    const res = await fetch(handoverSubmitUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload)
                    });

                    const json = await res.json();
                    if (!json.success) throw new Error(json.message || 'Lỗi lưu biên bản bàn giao ca.');

                    if (hoMsgBox) {
                        hoMsgBox.style.display = 'block';
                        hoMsgBox.style.background = '#E8F5E9';
                        hoMsgBox.style.color = '#2E7D32';
                        hoMsgBox.innerText = json.message || 'Đã bàn giao ca thành công!';
                    }

                    setTimeout(() => {
                        modalHandover.style.display = 'none';
                        formHandover.reset();
                        if (hoMsgBox) hoMsgBox.style.display = 'none';
                        window.location.reload();
                    }, 1200);
                } catch (err) {
                    if (hoMsgBox) {
                        hoMsgBox.style.display = 'block';
                        hoMsgBox.style.background = '#FFEBEE';
                        hoMsgBox.style.color = '#C62828';
                        hoMsgBox.innerText = err.message || 'Đã có lỗi xảy ra.';
                    }
                } finally {
                    if (submitBtn) submitBtn.disabled = false;
                }
            });
        }
    }
});

