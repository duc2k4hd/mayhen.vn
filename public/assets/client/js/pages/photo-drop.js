/**
 * Mây Hẹn (mayhen.vn) — Live Guest Photo Drop Native JavaScript
 * ES Modules & Zero External Libraries
 */

document.addEventListener('DOMContentLoaded', () => {
    const appEl = document.getElementById('mayhen_drop_app');
    if (!appEl) return;

    const form = document.getElementById('form_photo_drop');
    if (!form) return;

    const dropzone = document.getElementById('mayhen_dropzone');
    const fileInput = document.getElementById('input_drop_files');
    const promptEl = document.getElementById('dropzone_prompt');
    const previewsEl = document.getElementById('dropzone_previews');
    const progressWrap = document.getElementById('upload_progress_wrap');
    const progressFill = document.getElementById('upload_progress_fill');
    const progressLabel = document.getElementById('upload_progress_label');
    const submitBtn = document.getElementById('btn_submit_drop');
    const streamGrid = document.getElementById('stream_grid');

    const uploadUrl = appEl.dataset.uploadUrl;
    let selectedFiles = []; // Mảng chứa các File object đã chọn

    const MAX_FILES = 10;
    const MAX_FILE_SIZE_BYTES = 12 * 1024 * 1024; // 12MB
    const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'];

    /**
     * Cập nhật hiển thị giao diện xem trước danh sách ảnh
     */
    function renderPreviews() {
        if (selectedFiles.length === 0) {
            previewsEl.innerHTML = '';
            previewsEl.style.display = 'none';
            promptEl.style.display = 'block';
            return;
        }

        promptEl.style.display = 'none';
        previewsEl.style.display = 'grid';
        previewsEl.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'preview_item';

            const img = document.createElement('img');
            img.alt = file.name;
            const objectUrl = URL.createObjectURL(file);
            img.src = objectUrl;

            // Nút gỡ bỏ ảnh
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'preview_remove_btn';
            removeBtn.title = 'Gỡ bỏ ảnh này';
            removeBtn.innerHTML = '✕';
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                URL.revokeObjectURL(objectUrl);
                selectedFiles.splice(index, 1);
                renderPreviews();
            });

            item.appendChild(img);
            item.appendChild(removeBtn);
            previewsEl.appendChild(item);
        });
    }

    /**
     * Thêm tệp vào danh sách và kiểm tra validation
     */
    function handleFiles(files) {
        const fileList = Array.from(files);
        let errorMsg = null;

        for (const file of fileList) {
            if (selectedFiles.length >= MAX_FILES) {
                errorMsg = `Mỗi lần tải lên chỉ được chọn tối đa ${MAX_FILES} bức ảnh.`;
                break;
            }

            if (!ALLOWED_TYPES.includes(file.type.toLowerCase()) && !/\.(jpe?g|png|webp|heic|heif)$/i.test(file.name)) {
                errorMsg = `Tệp "${file.name}" không phải định dạng ảnh hợp lệ (JPG, PNG, WebP, HEIC).`;
                continue;
            }

            if (file.size > MAX_FILE_SIZE_BYTES) {
                errorMsg = `Ảnh "${file.name}" vượt quá dung lượng tối đa 12MB.`;
                continue;
            }

            // Tránh thêm file trùng
            const isDuplicate = selectedFiles.some(f => f.name === file.name && f.size === file.size);
            if (!isDuplicate) {
                selectedFiles.push(file);
            }
        }

        if (errorMsg) {
            alert(errorMsg);
        }

        renderPreviews();
    }

    // Sự kiện chọn tệp qua File Input
    fileInput.addEventListener('change', (e) => {
        if (e.target.files && e.target.files.length > 0) {
            handleFiles(e.target.files);
            // Reset input value để có thể chọn lại cùng một file nếu vừa xóa
            fileInput.value = '';
        }
    });

    // Kéo thả Dropzone
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('is-dragover');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('is-dragover');
        });
    });

    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        if (dt && dt.files && dt.files.length > 0) {
            handleFiles(dt.files);
        }
    });

    /**
     * Xử lý gửi Form tải ảnh qua AJAX với XMLHttpRequest để hiển thị progress bar
     */
    form.addEventListener('submit', (e) => {
        e.preventDefault();

        if (selectedFiles.length === 0) {
            alert('Vui lòng chọn ít nhất 1 bức ảnh để gửi tặng đôi uyên ương nhé.');
            return;
        }

        const uploaderNameInput = document.getElementById('uploader_name');
        if (!uploaderNameInput.value.trim()) {
            alert('Vui lòng nhập tên của bạn để cô dâu chú rể biết ai gửi ảnh nhé.');
            uploaderNameInput.focus();
            return;
        }

        const formData = new FormData(form);
        // Xóa trường photos rỗng từ file input và nạp các file thực tế từ selectedFiles
        formData.delete('photos[]');
        selectedFiles.forEach(file => {
            formData.append('photos[]', file);
        });

        // Bật trạng thái loading & progress
        submitBtn.disabled = true;
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.innerHTML = '⏳ Đang tải ảnh lên...';

        progressWrap.style.display = 'block';
        progressFill.style.width = '0%';
        progressLabel.textContent = 'Đang tải ảnh lên... 0%';

        const xhr = new XMLHttpRequest();
        xhr.open('POST', uploadUrl, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');

        // Theo dõi phần trăm tiến độ
        xhr.upload.onprogress = (evt) => {
            if (evt.lengthComputable) {
                const percent = Math.round((evt.loaded / evt.total) * 100);
                progressFill.style.width = `${percent}%`;
                progressLabel.textContent = `Đang tải ảnh lên... ${percent}%`;
            }
        };

        xhr.onload = () => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;

            let responseData = null;
            try {
                responseData = JSON.parse(xhr.responseText);
            } catch (err) {
                console.error('Không thể phân tích phản hồi JSON:', err);
            }

            if (xhr.status >= 200 && xhr.status < 300 && responseData && responseData.success) {
                progressLabel.textContent = '✓ Tải ảnh thành công!';
                progressFill.style.width = '100%';

                alert(responseData.message || 'Đã gửi ảnh thành công!');

                // Thêm ngay ảnh vào Stream nếu có danh sách trả về
                if (streamGrid && responseData.photos && responseData.photos.length > 0) {
                    responseData.photos.forEach(item => {
                        const card = document.createElement('div');
                        card.className = 'stream_photo_card';
                        card.innerHTML = `
                            <img src="${item.url}" alt="Khoảnh khắc từ ${item.uploader}" class="stream_img" loading="lazy">
                            <div class="stream_overlay">
                                <span class="stream_uploader">${item.uploader}</span>
                            </div>
                        `;
                        streamGrid.prepend(card);
                    });
                }

                // Reset form
                selectedFiles = [];
                renderPreviews();
                const captionInput = document.getElementById('caption');
                if (captionInput) captionInput.value = '';

                setTimeout(() => {
                    progressWrap.style.display = 'none';
                    progressFill.style.width = '0%';
                }, 2500);

            } else {
                let errorMsg = 'Có lỗi xảy ra khi tải ảnh lên. Vui lòng thử lại!';
                if (responseData && responseData.message) {
                    errorMsg = responseData.message;
                } else if (responseData && responseData.errors) {
                    const firstKey = Object.keys(responseData.errors)[0];
                    errorMsg = responseData.errors[firstKey][0];
                }
                alert(errorMsg);
                progressWrap.style.display = 'none';
            }
        };

        xhr.onerror = () => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            progressWrap.style.display = 'none';
            alert('Lỗi kết nối mạng khi tải ảnh lên. Vui lòng kiểm tra lại đường truyền của bạn!');
        };

        xhr.send(formData);
    });
});

