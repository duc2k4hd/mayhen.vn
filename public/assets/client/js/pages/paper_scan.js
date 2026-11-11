/**
 * Mây Hẹn (mayhen.vn) — Paper Invitation AI Parser JavaScript
 * Quy chuẩn: Native ES Modules, Vanilla JavaScript, Zero Frameworks.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Xử lý xem trước ảnh tải lên tại màn hình Create
    const fileInput = document.getElementById('mayhen_scan_file_input');
    const previewBox = document.getElementById('mayhen_scan_preview_box');
    const previewImg = document.getElementById('mayhen_scan_preview_img');
    const dropzone = document.getElementById('mayhen_scan_dropzone');
    const submitBtn = document.getElementById('mayhen_scan_submit_btn');

    if (fileInput && previewImg) {
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    previewImg.src = event.target.result;
                    if (previewBox) previewBox.style.display = 'block';
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.style.opacity = '1';
                    }
                };
                reader.readAsDataURL(file);
            }
        });

        // Hỗ trợ Kéo thả (Drag & Drop)
        if (dropzone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('is-dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('is-dragover');
                }, false);
            });

            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            }, false);
        }
    }

    // 2. Xử lý Zoom & Xoay ảnh đối chiếu tại màn hình Review
    const targetImg = document.getElementById('mayhen_review_target_img');
    const zoomInBtn = document.getElementById('mayhen_zoom_in_btn');
    const zoomOutBtn = document.getElementById('mayhen_zoom_out_btn');
    const zoomResetBtn = document.getElementById('mayhen_zoom_reset_btn');
    const rotateBtn = document.getElementById('mayhen_rotate_btn');

    if (targetImg) {
        let currentScale = 1.0;
        let currentRotation = 0;

        function updateTransform() {
            targetImg.style.transform = `scale(${currentScale}) rotate(${currentRotation}deg)`;
        }

        if (zoomInBtn) {
            zoomInBtn.addEventListener('click', () => {
                currentScale = Math.min(currentScale + 0.25, 3.0);
                updateTransform();
            });
        }

        if (zoomOutBtn) {
            zoomOutBtn.addEventListener('click', () => {
                currentScale = Math.max(currentScale - 0.25, 0.5);
                updateTransform();
            });
        }

        if (zoomResetBtn) {
            zoomResetBtn.addEventListener('click', () => {
                currentScale = 1.0;
                currentRotation = 0;
                updateTransform();
            });
        }

        if (rotateBtn) {
            rotateBtn.addEventListener('click', () => {
                currentRotation = (currentRotation + 90) % 360;
                updateTransform();
            });
        }
    }
});

