/**
 * MAYHEN.VN — INVITATION LIVE EDITOR CONTROLLER
 * Schema-Driven Dynamic Stepper & Realtime Iframe Template Sync
 * Strict: Pure Vanilla JavaScript (ES Modules), Zero external dependencies
 */
import { $, $$, on } from '../common/dom.js';

document.addEventListener('DOMContentLoaded', () => {
    const mainForm = $('#mayhen_editor_form');
    const previewIframe = $('#mayhen_editor_preview_iframe');
    const mobileIframe = $('#mayhen_editor_mobile_preview_iframe');
    const dockNames = $('[data-mayhen-preview="names"]');

    // ----------------------------------------------------
    // 1. Dynamic Stepper Navigation
    // ----------------------------------------------------
    let currentStep = 1;
    const stepTabs = $$('[data-mayhen-step]');
    const stepPanels = $$('[data-step-panel]');
    const totalSteps = stepPanels.length;
    const btnPrev = $('#mayhen_btn_prev');
    const btnNext = $('#mayhen_btn_next');

    const goToStep = (step) => {
        if (step < 1 || step > totalSteps) return;
        currentStep = step;

        // Cập nhật tab navigation pills
        stepTabs.forEach(tab => {
            const s = parseInt(tab.getAttribute('data-mayhen-step'), 10);
            if (s === currentStep) {
                tab.classList.add('mayhen_editor_stepper_tab_active');
                tab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } else {
                tab.classList.remove('mayhen_editor_stepper_tab_active');
            }
        });

        // Cập nhật step panels
        stepPanels.forEach(panel => {
            const p = parseInt(panel.getAttribute('data-step-panel'), 10);
            if (p === currentStep) {
                panel.classList.add('mayhen_editor_step_active');
            } else {
                panel.classList.remove('mayhen_editor_step_active');
            }
        });

        // Ẩn hiện nút Quay lại / Tiếp theo
        if (btnPrev) {
            btnPrev.style.display = currentStep > 1 ? 'inline-flex' : 'none';
        }

        if (btnNext) {
            if (currentStep === totalSteps) {
                btnNext.innerHTML = `<span>Xem Lại & Tạo Thiệp</span> <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>`;
            } else {
                btnNext.innerHTML = `<span>Tiếp Tục</span> <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>`;
            }
        }
    };

    stepTabs.forEach(tab => {
        on(tab, 'click', () => {
            const s = parseInt(tab.getAttribute('data-mayhen-step'), 10);
            goToStep(s);
        });
    });

    if (btnPrev) {
        on(btnPrev, 'click', () => {
            goToStep(currentStep - 1);
        });
    }

    if (btnNext) {
        on(btnNext, 'click', () => {
            if (currentStep < totalSteps) {
                goToStep(currentStep + 1);
            } else {
                submitFormWithPendingUploads();
            }
        });
    }

    // ----------------------------------------------------
    // 2. Realtime Iframe Communication Engine (PostMessage Sync)
    // ----------------------------------------------------
    const sendSyncMessage = (key, value) => {
        const payload = {
            type: 'MAYHEN_SYNC_INPUT',
            key: key,
            value: value,
        };

        try {
            if (previewIframe && previewIframe.contentWindow) {
                previewIframe.contentWindow.postMessage(payload, '*');
            }
            if (mobileIframe && mobileIframe.contentWindow) {
                mobileIframe.contentWindow.postMessage(payload, '*');
            }
        } catch (e) {
            // Safe cross-origin fallback
        }
    };

    const updateDockNames = () => {
        if (!dockNames) return;
        const groomInp = document.querySelector('[data-sync-key="wedding.groom_name"]');
        const brideInp = document.querySelector('[data-sync-key="wedding.bride_name"]');
        const g = groomInp ? groomInp.value.trim() : 'Tràng Đức';
        const b = brideInp ? brideInp.value.trim() : 'Ngọc Phượng';
        dockNames.textContent = `${g || 'Chú Rể'} • ${b || 'Cô Dâu'}`;
    };

    if (mainForm) {
        const handleInputEvent = (e) => {
            const target = e.target;
            const key = target.getAttribute('data-sync-key');
            if (!key) return;

            const val = target.value;

            // Xem trước ảnh nhỏ nếu là trường image
            if (target.id) {
                const thumb = document.getElementById('preview_thumb_' + target.id);
                if (thumb && val) {
                    thumb.src = val;
                }
            }

            // Cập nhật tên đôi uyên ương trên mobile dock
            if (key === 'wedding.groom_name' || key === 'wedding.bride_name') {
                updateDockNames();
            }

            // Đồng bộ sang Iframe
            sendSyncMessage(key, val);
        };

        on(mainForm, 'input', handleInputEvent);
        on(mainForm, 'change', handleInputEvent);
    }

    // Khi Iframe hoàn tất tải, gửi toàn bộ giá trị form hiện thời sang iframe
    const syncAllFieldsToIframe = (iframeEl) => {
        if (!iframeEl || !iframeEl.contentWindow) return;
        const inputs = $$('[data-sync-key]');
        inputs.forEach(inp => {
            const key = inp.getAttribute('data-sync-key');
            const val = inp.value;
            if (key && val) {
                iframeEl.contentWindow.postMessage({
                    type: 'MAYHEN_SYNC_INPUT',
                    key: key,
                    value: val,
                }, '*');
            }
        });
    };

    if (previewIframe) {
        previewIframe.addEventListener('load', () => {
            setTimeout(() => syncAllFieldsToIframe(previewIframe), 300);
        });
    }

    if (mobileIframe) {
        mobileIframe.addEventListener('load', () => {
            setTimeout(() => syncAllFieldsToIframe(mobileIframe), 300);
        });
    }

    // ----------------------------------------------------
    // 3. Asset Image Uploader (Upload trực tiếp vào assets của template)
    // ----------------------------------------------------
    const fileUploaders = $$('.mayhen_editor_file_uploader');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                      document.querySelector('input[name="_token"]')?.value || '';

    // ----------------------------------------------------
    // 3. Quản lý Ảnh: Lưu trữ Client-side, Kiểm tra tỷ lệ 3/4 & Cropper Canvas
    // ----------------------------------------------------
    const pendingUploadBlobs = new Map(); // targetInputId -> Blob / File
    let activeCropperTarget = null; // { input, thumb, status, file, img, scale, rotate, offsetX, offsetY }

    const cropperModal = $('#mayhen_cropper_modal');
    const cropperCanvas = $('#mayhen_cropper_canvas');
    const cropperStage = $('#mayhen_cropper_stage');
    const cropperOverlay = $('#mayhen_cropper_overlay');
    const cropperCloseBtn = $('#mayhen_cropper_close_btn');
    const cropperCancelBtn = $('#mayhen_cropper_btn_cancel');
    const cropperConfirmBtn = $('#mayhen_cropper_btn_confirm');
    const cropperZoomIn = $('#mayhen_cropper_zoom_in');
    const cropperZoomOut = $('#mayhen_cropper_zoom_out');
    const cropperRotateLeft = $('#mayhen_cropper_rotate_left');
    const cropperRotateRight = $('#mayhen_cropper_rotate_right');
    const cropperReset = $('#mayhen_cropper_reset');

    let cropperCtx = cropperCanvas ? cropperCanvas.getContext('2d') : null;
    let isDraggingStage = false;
    let startDragX = 0;
    let startDragY = 0;

    const renderCropper = () => {
        if (!activeCropperTarget || !activeCropperTarget.img || !cropperCtx) return;
        const img = activeCropperTarget.img;
        const width = 600;
        const height = 400;

        cropperCanvas.width = width;
        cropperCanvas.height = height;

        cropperCtx.clearRect(0, 0, width, height);
        cropperCtx.save();
        cropperCtx.translate(width / 2 + activeCropperTarget.offsetX, height / 2 + activeCropperTarget.offsetY);
        cropperCtx.rotate((activeCropperTarget.rotate * Math.PI) / 180);
        cropperCtx.scale(activeCropperTarget.scale, activeCropperTarget.scale);

        cropperCtx.drawImage(img, -img.naturalWidth / 2, -img.naturalHeight / 2);
        cropperCtx.restore();

        // Kích thước khung 3:4 ở chính giữa
        const cropBoxHeight = 320;
        const cropBoxWidth = (cropBoxHeight * 3) / 4; // 240px
        const cropBoxX = (width - cropBoxWidth) / 2;
        const cropBoxY = (height - cropBoxHeight) / 2;

        if (cropperOverlay) {
            cropperOverlay.style.width = cropBoxWidth + 'px';
            cropperOverlay.style.height = cropBoxHeight + 'px';
            cropperOverlay.style.left = cropBoxX + 'px';
            cropperOverlay.style.top = cropBoxY + 'px';
        }
    };

    const openCropperModal = (imageSource, targetObj) => {
        const img = new Image();
        img.onload = () => {
            activeCropperTarget = {
                ...targetObj,
                img: img,
                scale: 1,
                rotate: 0,
                offsetX: 0,
                offsetY: 0,
            };

            // Tính scale khởi đầu để ảnh vừa ít nhất khớp với khung 3/4
            const minScaleW = 240 / img.naturalWidth;
            const minScaleH = 320 / img.naturalHeight;
            activeCropperTarget.scale = Math.max(minScaleW, minScaleH, 0.4);

            if (cropperModal) {
                cropperModal.classList.add('mayhen_editor_modal_open');
                document.body.style.overflow = 'hidden';
            }
            renderCropper();
        };
        img.src = imageSource;
    };

    const closeCropperModal = () => {
        if (cropperModal) cropperModal.classList.remove('mayhen_editor_modal_open');
        document.body.style.overflow = '';
        activeCropperTarget = null;
    };

    if (cropperCloseBtn) on(cropperCloseBtn, 'click', closeCropperModal);
    if (cropperCancelBtn) on(cropperCancelBtn, 'click', closeCropperModal);

    if (cropperZoomIn) on(cropperZoomIn, 'click', () => {
        if (!activeCropperTarget) return;
        activeCropperTarget.scale = Math.min(3, activeCropperTarget.scale * 1.15);
        renderCropper();
    });

    if (cropperZoomOut) on(cropperZoomOut, 'click', () => {
        if (!activeCropperTarget) return;
        activeCropperTarget.scale = Math.max(0.1, activeCropperTarget.scale / 1.15);
        renderCropper();
    });

    if (cropperRotateLeft) on(cropperRotateLeft, 'click', () => {
        if (!activeCropperTarget) return;
        activeCropperTarget.rotate = (activeCropperTarget.rotate - 90) % 360;
        renderCropper();
    });

    if (cropperRotateRight) on(cropperRotateRight, 'click', () => {
        if (!activeCropperTarget) return;
        activeCropperTarget.rotate = (activeCropperTarget.rotate + 90) % 360;
        renderCropper();
    });

    if (cropperReset) on(cropperReset, 'click', () => {
        if (!activeCropperTarget || !activeCropperTarget.img) return;
        activeCropperTarget.scale = Math.max(240 / activeCropperTarget.img.naturalWidth, 320 / activeCropperTarget.img.naturalHeight, 0.4);
        activeCropperTarget.rotate = 0;
        activeCropperTarget.offsetX = 0;
        activeCropperTarget.offsetY = 0;
        renderCropper();
    });

    // Kéo di chuyển ảnh trong khung cắt
    if (cropperStage) {
        on(cropperStage, 'mousedown', (e) => {
            isDraggingStage = true;
            startDragX = e.clientX;
            startDragY = e.clientY;
        });

        window.addEventListener('mousemove', (e) => {
            if (!isDraggingStage || !activeCropperTarget) return;
            const dx = e.clientX - startDragX;
            const dy = e.clientY - startDragY;
            startDragX = e.clientX;
            startDragY = e.clientY;

            activeCropperTarget.offsetX += dx;
            activeCropperTarget.offsetY += dy;
            renderCropper();
        });

        window.addEventListener('mouseup', () => {
            isDraggingStage = false;
        });
    }

    // Xác nhận cắt ảnh và xuất ra đúng tỷ lệ 3/4
    if (cropperConfirmBtn) {
        on(cropperConfirmBtn, 'click', () => {
            if (!activeCropperTarget || !activeCropperTarget.img) return;

            // Xuất ra canvas chất lượng cao độ phân giải chuẩn 3/4: 900x1200
            const outW = 900;
            const outH = 1200;
            const outCanvas = document.createElement('canvas');
            outCanvas.width = outW;
            outCanvas.height = outH;
            const outCtx = outCanvas.getContext('2d');

            const stageW = 600;
            const stageH = 400;
            const cropBoxW = 240;
            const cropBoxH = 320;
            const scaleFactor = outW / cropBoxW; // 900 / 240 = 3.75

            outCtx.save();
            outCtx.translate(outW / 2 + activeCropperTarget.offsetX * scaleFactor, outH / 2 + activeCropperTarget.offsetY * scaleFactor);
            outCtx.rotate((activeCropperTarget.rotate * Math.PI) / 180);
            outCtx.scale(activeCropperTarget.scale * scaleFactor, activeCropperTarget.scale * scaleFactor);
            outCtx.drawImage(activeCropperTarget.img, -activeCropperTarget.img.naturalWidth / 2, -activeCropperTarget.img.naturalHeight / 2);
            outCtx.restore();

            outCanvas.toBlob((blob) => {
                if (!blob) return;
                const croppedUrl = URL.createObjectURL(blob);
                const targetInput = activeCropperTarget.input;
                const targetThumb = activeCropperTarget.thumb;
                const statusEl = activeCropperTarget.status;

                if (targetThumb) targetThumb.src = croppedUrl;
                if (targetInput) {
                    targetInput.value = croppedUrl;
                    pendingUploadBlobs.set(targetInput.id, blob);
                    const syncKey = targetInput.getAttribute('data-sync-key');
                    if (syncKey) sendSyncMessage(syncKey, croppedUrl);
                }

                if (statusEl) {
                    statusEl.textContent = '✓ Đã căn chỉnh 3/4 chuẩn xác';
                    statusEl.style.color = '#10B981';
                }

                closeCropperModal();
            }, 'image/jpeg', 0.92);
        });
    }

    // Sự kiện khi người dùng chọn file ảnh từ máy
    fileUploaders.forEach(uploader => {
        on(uploader, 'change', (e) => {
            const file = e.target.files?.[0];
            if (!file) return;

            const targetInputId = uploader.getAttribute('data-target-input');
            const targetThumbId = uploader.getAttribute('data-target-thumb');
            const isMainPhoto = uploader.getAttribute('data-is-main-photo') === '1';
            const targetInput = document.getElementById(targetInputId);
            const targetThumb = document.getElementById(targetThumbId);
            const statusEl = document.getElementById('status_' + targetInputId);

            const objectUrl = URL.createObjectURL(file);

            // Kiểm tra kích thước và tỷ lệ ảnh
            const tempImg = new Image();
            tempImg.onload = () => {
                const ratio = tempImg.naturalWidth / tempImg.naturalHeight;
                const isThreeFourRatio = Math.abs(ratio - 0.75) <= 0.04; // Tỷ lệ 3/4 = 0.75

                if (isMainPhoto && !isThreeFourRatio) {
                    // Nếu là ảnh chính và không đúng tỷ lệ 3/4 -> Mở popup cắt ảnh ngay
                    openCropperModal(objectUrl, {
                        input: targetInput,
                        thumb: targetThumb,
                        status: statusEl,
                        file: file,
                    });
                } else {
                    // Tỷ lệ hợp lệ hoặc ảnh phụ -> Lưu blob ở client chờ bấm Tạo Thiệp
                    pendingUploadBlobs.set(targetInputId, file);
                    if (targetThumb) targetThumb.src = objectUrl;
                    if (targetInput) {
                        targetInput.value = objectUrl;
                        const syncKey = targetInput.getAttribute('data-sync-key');
                        if (syncKey) sendSyncMessage(syncKey, objectUrl);
                    }
                    if (statusEl) {
                        statusEl.textContent = '✓ Đã chọn (sẽ lưu khi tạo thiệp)';
                        statusEl.style.color = 'var(--mayhen-color-primary)';
                    }
                }
            };
            tempImg.src = objectUrl;
        });
    });

    // ----------------------------------------------------
    // 3.1 Tự động tạo mã VietQR Ngân Hàng An Toàn Tuyệt Đối
    // ----------------------------------------------------
    const updateVietQr = (side) => {
        const bankSelect = $(`.mayhen_vietqr_bank_select[data-qr-side="${side}"]`);
        const accInput = $(`.mayhen_vietqr_acc_input[data-qr-side="${side}"]`);
        const nameInput = $(`.mayhen_vietqr_name_input[data-qr-side="${side}"]`);

        if (!bankSelect || !accInput) return;

        const bankCode = bankSelect.value.trim();
        const accountNo = accInput.value.trim();
        const accountName = nameInput ? nameInput.value.trim().toUpperCase() : '';
        const targetInputId = bankSelect.getAttribute('data-target-input');
        const targetInput = document.getElementById(targetInputId);
        const targetThumb = document.getElementById('preview_thumb_' + targetInputId);

        if (!bankCode || !accountNo) return;

        // Sinh link VietQR chuẩn NAPAS an toàn 100%
        let qrUrl = `https://img.vietqr.io/image/${bankCode}-${accountNo}-compact2.jpg`;
        if (accountName) {
            qrUrl += `?accountName=${encodeURIComponent(accountName)}`;
        }

        if (targetThumb) targetThumb.src = qrUrl;
        if (targetInput) {
            targetInput.value = qrUrl;
            const syncKey = targetInput.getAttribute('data-sync-key');
            if (syncKey) sendSyncMessage(syncKey, qrUrl);
        }
    };

    $$('.mayhen_vietqr_bank_select, .mayhen_vietqr_acc_input, .mayhen_vietqr_name_input').forEach(el => {
        on(el, 'input', () => {
            const side = el.getAttribute('data-qr-side');
            if (side) updateVietQr(side);
        });
    });

    $$('.mayhen_btn_gen_vietqr').forEach(btn => {
        on(btn, 'click', () => {
            const side = btn.getAttribute('data-qr-side');
            if (side) updateVietQr(side);
        });
    });

    // ----------------------------------------------------
    // 3.2 Modal Chọn Nhạc Cưới Lãng Mạn Bản Quyền
    // ----------------------------------------------------
    const musicModal = $('#mayhen_music_picker_modal');
    const musicCloseBtn = $('#mayhen_music_close_btn');
    const musicSearchInput = $('#mayhen_music_search_input');
    const musicListContainer = $('#mayhen_music_list');
    const musicPreviewAudio = $('#mayhen_music_preview_audio');
    let musicSearchDebounce = null;
    let currentPlayingBtn = null;

    const openMusicModal = () => {
        if (musicModal) {
            musicModal.classList.add('mayhen_editor_modal_open');
            document.body.style.overflow = 'hidden';
            fetchMusics('');
        }
    };

    const closeMusicModal = () => {
        if (musicModal) musicModal.classList.remove('mayhen_editor_modal_open');
        document.body.style.overflow = '';
        if (musicPreviewAudio) {
            musicPreviewAudio.pause();
            if (currentPlayingBtn) currentPlayingBtn.classList.remove('mayhen_playing');
        }
    };

    const fetchMusics = async (query = '') => {
        if (!musicListContainer) return;
        musicListContainer.innerHTML = '<p style="text-align:center;padding:24px;color:var(--mayhen-color-text-muted);font-style:italic;">Đang tìm kiếm giai điệu...</p>';

        try {
            const res = await fetch(`/api/wedding-musics?q=${encodeURIComponent(query)}`);
            const json = await res.json();
            const list = json.data || [];

            if (list.length === 0) {
                musicListContainer.innerHTML = '<p style="text-align:center;padding:24px;color:var(--mayhen-color-text-muted);">Không tìm thấy bài hát nào phù hợp.</p>';
                return;
            }

            musicListContainer.innerHTML = '';
            list.forEach(song => {
                const item = document.createElement('div');
                item.className = 'mayhen_music_item';
                item.innerHTML = `
                    <div class="mayhen_music_info">
                        <button type="button" class="mayhen_music_play_btn" data-audio-url="${song.url}" title="Nghe thử">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </button>
                        <div class="mayhen_music_meta">
                            <div class="mayhen_music_title">${song.title}</div>
                            <div class="mayhen_music_artist">Ca sĩ / Tác giả: <strong>${song.artist}</strong></div>
                        </div>
                    </div>
                    <button type="button" class="mayhen_music_select_btn" data-audio-url="${song.url}" data-audio-title="${song.title} - ${song.artist}">
                        Chọn bài này
                    </button>
                `;
                musicListContainer.appendChild(item);
            });

            // Gắn sự kiện nghe thử và chọn bài
            musicListContainer.querySelectorAll('.mayhen_music_play_btn').forEach(btn => {
                on(btn, 'click', () => {
                    const audioUrl = btn.getAttribute('data-audio-url');
                    if (musicPreviewAudio.src === audioUrl && !musicPreviewAudio.paused) {
                        musicPreviewAudio.pause();
                        btn.classList.remove('mayhen_playing');
                    } else {
                        if (currentPlayingBtn) currentPlayingBtn.classList.remove('mayhen_playing');
                        musicPreviewAudio.src = audioUrl;
                        musicPreviewAudio.play();
                        btn.classList.add('mayhen_playing');
                        currentPlayingBtn = btn;
                    }
                });
            });

            musicListContainer.querySelectorAll('.mayhen_music_select_btn').forEach(btn => {
                on(btn, 'click', () => {
                    const audioUrl = btn.getAttribute('data-audio-url');
                    const musicInput = document.getElementById('wedding_music_url') || $('[data-sync-key="wedding.music_url"]');
                    if (musicInput) {
                        musicInput.value = audioUrl;
                        sendSyncMessage('wedding.music_url', audioUrl);
                    }
                    closeMusicModal();
                });
            });
        } catch {
            musicListContainer.innerHTML = '<p style="text-align:center;padding:24px;color:var(--mayhen-color-danger);">Không thể kết nối đến kho nhạc.</p>';
        }
    };

    if (musicCloseBtn) on(musicCloseBtn, 'click', closeMusicModal);
    if (musicSearchInput) {
        on(musicSearchInput, 'input', (e) => {
            clearTimeout(musicSearchDebounce);
            musicSearchDebounce = setTimeout(() => {
                fetchMusics(e.target.value.trim());
            }, 250);
        });
    }

    $$('[data-mayhen-action="open-music-modal"]').forEach(btn => {
        on(btn, 'click', openMusicModal);
    });

    // ----------------------------------------------------
    // 3.3 Submit Form: Tải các ảnh blob thực sự lên server khi bấm Tạo Thiệp
    // ----------------------------------------------------
    const submitFormWithPendingUploads = async () => {
        const submitBtns = $$('[data-mayhen-action="trigger-submit"]');
        submitBtns.forEach(btn => {
            btn.disabled = true;
            btn.innerHTML = '<span>Đang chuẩn bị lưu thiệp cưới...</span>';
        });

        const templateSlug = window.__MAYHEN_TEMPLATE_SLUG || document.getElementById('template_id')?.value || 'net-muc-tinh-yeu';
        const csrfToken = window.__MAYHEN_CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || '';
        const uploadUrl = window.__MAYHEN_UPLOAD_URL || '/tao-thiep/upload-asset';

        // 1. Tải toàn bộ các file ảnh blob lên server
        if (pendingUploadBlobs.size > 0) {
            submitBtns.forEach(btn => {
                btn.innerHTML = '<span>Đang tải ảnh cưới lên hệ thống...</span>';
            });

            const uploadPromises = [];
            for (const [inputId, blobOrFile] of pendingUploadBlobs.entries()) {
                const targetInput = document.getElementById(inputId);
                const fileUploader = document.querySelector(`input[type="file"][data-target-input="${inputId}"]`);
                const actualSlug = fileUploader?.getAttribute('data-template-slug') || templateSlug;
                const statusEl = document.getElementById('status_' + inputId);
                if (statusEl) statusEl.textContent = 'Đang lưu ảnh...';

                const formData = new FormData();
                formData.append('file', blobOrFile, 'wedding_photo.jpg');
                formData.append('template_slug', actualSlug);
                formData.append('_token', csrfToken);

                const uploadPromise = fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData,
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.file_url) {
                        if (targetInput) targetInput.value = data.file_url;
                        if (statusEl) {
                            statusEl.textContent = '✓ Đã lưu ảnh';
                            statusEl.style.color = '#10B981';
                        }
                    }
                })
                .catch(err => {
                    console.error('Lỗi tải ảnh:', err);
                });

                uploadPromises.push(uploadPromise);
            }

            try {
                await Promise.all(uploadPromises);
            } catch (err) {
                console.error('Một số ảnh chưa kịp lưu:', err);
            }
        }

        // 2. Bảo vệ chống rò rỉ URL dạng blob: vào database
        if (mainForm) {
            const allInputs = mainForm.querySelectorAll('input');
            allInputs.forEach(inp => {
                if (typeof inp.value === 'string' && inp.value.startsWith('blob:')) {
                    inp.value = ''; // Xóa sạch URL blob tạm thời để backend tự fallback về ảnh mẫu
                }
            });

            submitBtns.forEach(btn => {
                btn.innerHTML = '<span>Đang xuất bản thiệp cưới...</span>';
            });

            mainForm.submit();
        }
    };

    const submitTriggers = $$('[data-mayhen-action="trigger-submit"]');
    submitTriggers.forEach(btn => {
        on(btn, 'click', (e) => {
            e.preventDefault();
            submitFormWithPendingUploads();
        });
    });

    // ----------------------------------------------------
    // 4. Mobile Fullscreen Preview Modal
    // ----------------------------------------------------
    const previewModal = $('#mayhen_mobile_preview_modal');
    const openPreviewBtn = $('[data-mayhen-action="open-preview-modal"]');
    const closePreviewBtn = $('[data-mayhen-action="close-preview-modal"]');

    if (openPreviewBtn && previewModal) {
        on(openPreviewBtn, 'click', () => {
            previewModal.classList.add('mayhen_editor_modal_open');
            document.body.style.overflow = 'hidden';
            if (mobileIframe) {
                syncAllFieldsToIframe(mobileIframe);
            }
        });
    }

    if (closePreviewBtn && previewModal) {
        on(closePreviewBtn, 'click', () => {
            previewModal.classList.remove('mayhen_editor_modal_open');
            document.body.style.overflow = '';
        });
    }

    if (previewModal) {
        on(previewModal, 'click', (e) => {
            if (e.target === previewModal) {
                previewModal.classList.remove('mayhen_editor_modal_open');
                document.body.style.overflow = '';
            }
        });
    }

    // ----------------------------------------------------
    // 4. Template Switcher Modal (Đổi Mẫu Nhanh)
    // ----------------------------------------------------
    const templateModal = $('#mayhen_template_switcher_modal');
    const openTemplateBtn = $('[data-mayhen-action="open-template-modal"]');
    const closeTemplateBtn = $('[data-mayhen-action="close-template-modal"]');

    if (openTemplateBtn && templateModal) {
        on(openTemplateBtn, 'click', () => {
            templateModal.classList.add('mayhen_editor_modal_open');
            document.body.style.overflow = 'hidden';
        });
    }

    if (closeTemplateBtn && templateModal) {
        on(closeTemplateBtn, 'click', () => {
            templateModal.classList.remove('mayhen_editor_modal_open');
            document.body.style.overflow = '';
        });
    }

    if (templateModal) {
        on(templateModal, 'click', (e) => {
            if (e.target === templateModal) {
                templateModal.classList.remove('mayhen_editor_modal_open');
                document.body.style.overflow = '';
            }
        });
    }

    // Khởi chạy dock
    updateDockNames();
});
