document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('mayhenAdminNewsForm');
    if (!form) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const uploadUrl = form.dataset.uploadUrl || '';
    const mediaUrl = form.dataset.mediaUrl || '';
    const basePublicUrl = form.dataset.publicBaseUrl || 'https://mayhen.vn/tin-tuc';

    const titleInput = document.getElementById('mayhenNewsTitle');
    const slugInput = document.getElementById('mayhenNewsSlug');
    const regenSlugBtn = document.getElementById('mayhenRegenSlugBtn');
    const summaryInput = document.getElementById('mayhenNewsSummary');
    const metaTitleInput = document.getElementById('mayhenNewsMetaTitle');
    const metaDescInput = document.getElementById('mayhenNewsMetaDesc');
    const metaTitleCounter = document.getElementById('mayhenMetaTitleCounter');
    const metaDescCounter = document.getElementById('mayhenMetaDescCounter');
    const serpUrl = document.getElementById('mayhenSerpUrl');
    const serpTitle = document.getElementById('mayhenSerpTitle');
    const serpDesc = document.getElementById('mayhenSerpDesc');
    const statsPill = document.getElementById('mayhenEditorStats');

    const slugifyVi = (str) => {
        return String(str || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/Đ/g, 'd')
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, ' ')
            .trim()
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    };

    let slugTouched = Boolean(slugInput && slugInput.value.trim() !== '');

    const updateSeoPreview = () => {
        const titleVal = (metaTitleInput?.value || titleInput?.value || 'Tiêu đề bản tin Mây Hẹn').trim();
        const descVal = (metaDescInput?.value || summaryInput?.value || 'Mô tả SEO hiển thị trên kết quả tìm kiếm Google...').trim();
        const slugVal = (slugInput?.value || slugifyVi(titleInput?.value || 'duong-dan-ban-tin')).trim();

        if (serpUrl) {
            serpUrl.textContent = `${basePublicUrl.replace(/\/$/, '')}/${slugVal || 'ban-tin-moi'}`;
        }
        if (serpTitle) {
            serpTitle.textContent = titleVal;
        }
        if (serpDesc) {
            serpDesc.textContent = descVal.slice(0, 170);
        }

        if (metaTitleCounter) {
            const len = (metaTitleInput?.value || titleInput?.value || '').trim().length;
            metaTitleCounter.textContent = `${len}/60 ký tự`;
            metaTitleCounter.className = 'mayhen_admin_news_counter' + (len >= 45 && len <= 65 ? ' is-good' : (len > 65 ? ' is-warn' : ''));
        }
        if (metaDescCounter) {
            const len = (metaDescInput?.value || summaryInput?.value || '').trim().length;
            metaDescCounter.textContent = `${len}/160 ký tự`;
            metaDescCounter.className = 'mayhen_admin_news_counter' + (len >= 120 && len <= 165 ? ' is-good' : (len > 165 ? ' is-warn' : ''));
        }
    };

    titleInput?.addEventListener('input', () => {
        if (!slugTouched && slugInput) {
            slugInput.value = slugifyVi(titleInput.value);
        }
        updateSeoPreview();
    });

    slugInput?.addEventListener('input', () => {
        slugTouched = slugInput.value.trim() !== '';
        updateSeoPreview();
    });

    regenSlugBtn?.addEventListener('click', () => {
        if (slugInput && titleInput) {
            slugInput.value = slugifyVi(titleInput.value);
            slugTouched = true;
            updateSeoPreview();
        }
    });

    [summaryInput, metaTitleInput, metaDescInput].forEach((el) => {
        el?.addEventListener('input', updateSeoPreview);
    });
    updateSeoPreview();

    // =========================================================================
    // COVER IMAGE DRAG & DROP + LIVE PREVIEW
    // =========================================================================
    const coverInput = document.getElementById('mayhenCoverFileInput');
    const coverUrlHidden = document.getElementById('mayhenCoverUrlHidden');
    const removeCoverHidden = document.getElementById('mayhenRemoveCoverHidden');
    const coverDropzone = document.getElementById('mayhenCoverDropzone');
    const coverPreviewWrap = document.getElementById('mayhenCoverPreviewWrap');
    const coverPreviewImg = document.getElementById('mayhenCoverPreviewImg');
    const coverRemoveBtn = document.getElementById('mayhenCoverRemoveBtn');

    const showCoverPreview = (src) => {
        if (!coverPreviewWrap || !coverPreviewImg) {
            return;
        }
        coverPreviewImg.src = src;
        coverPreviewWrap.classList.remove('mayhen_admin_news_hidden');
        if (removeCoverHidden) {
            removeCoverHidden.value = '0';
        }
    };

    coverDropzone?.addEventListener('click', () => coverInput?.click());
    coverDropzone?.addEventListener('dragover', (e) => {
        e.preventDefault();
        coverDropzone.classList.add('is-dragover');
    });
    coverDropzone?.addEventListener('dragleave', () => {
        coverDropzone.classList.remove('is-dragover');
    });
    coverDropzone?.addEventListener('drop', (e) => {
        e.preventDefault();
        coverDropzone.classList.remove('is-dragover');
        if (e.dataTransfer?.files?.length && coverInput) {
            coverInput.files = e.dataTransfer.files;
            coverInput.dispatchEvent(new Event('change'));
        }
    });

    coverInput?.addEventListener('change', () => {
        const file = coverInput.files?.[0];
        if (!file) {
            return;
        }
        if (coverUrlHidden) {
            coverUrlHidden.value = '';
        }
        const reader = new FileReader();
        reader.onload = (ev) => {
            if (typeof ev.target?.result === 'string') {
                showCoverPreview(ev.target.result);
            }
        };
        reader.readAsDataURL(file);
    });

    coverRemoveBtn?.addEventListener('click', () => {
        if (coverInput) {
            coverInput.value = '';
        }
        if (coverUrlHidden) {
            coverUrlHidden.value = '';
        }
        if (removeCoverHidden) {
            removeCoverHidden.value = '1';
        }
        coverPreviewWrap?.classList.add('mayhen_admin_news_hidden');
    });

    // =========================================================================
    // LOCAL SELF-HOSTED TINYMCE 6 INITIALIZATION
    // =========================================================================
    const updateWordStats = (htmlContent) => {
        if (!statsPill) {
            return;
        }
        const text = String(htmlContent || '')
            .replace(/<[^>]+>/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
        const words = text ? text.split(' ').length : 0;
        const mins = Math.max(1, Math.ceil(words / 200));
        statsPill.textContent = `${words.toLocaleString('vi-VN')} từ • ~${mins} phút đọc`;
    };

    const uploadImageToServer = async (blobOrFile, filename) => {
        const fd = new FormData();
        fd.append('file', blobOrFile, filename || 'news-image.png');
        const res = await fetch(uploadUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: fd,
        });
        if (!res.ok) {
            throw new Error('Không thể tải ảnh lên máy chủ.');
        }
        const data = await res.json();
        if (!data.location) {
            throw new Error('Phản hồi tải ảnh không hợp lệ.');
        }
        return data.location;
    };

    if (typeof window.tinymce !== 'undefined') {
        window.tinymce.init({
            selector: '#mayhenAdminNewsContent',
            height: 560,
            menubar: 'file edit view insert format tools table',
            branding: false,
            promotion: false,
            skin: 'oxide',
            content_css: 'default',
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
                'preview', 'anchor', 'searchreplace', 'visualblocks', 'code',
                'fullscreen', 'insertdatetime', 'media', 'table', 'wordcount',
            ],
            toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist | blockquote table | link image mayhenMediaPicker | removeformat code fullscreen preview',
            block_formats: 'Đoạn văn=p; Tiêu đề H2 (Mục lục)=h2; Tiêu đề H3 (Mục con)=h3; Tiêu đề H4=h4; Trích dẫn=blockquote',
            image_caption: true,
            image_advtab: true,
            automatic_uploads: true,
            relative_urls: false,
            remove_script_host: true,
            convert_urls: false,
            content_style: `
                body { font-family: 'Open Sans', -apple-system, sans-serif; font-size: 15.5px; line-height: 1.75; color: #2B241F; padding: 12px 18px; max-width: 860px; margin: 0 auto; }
                h2 { font-size: 1.45em; color: #1F1D1A; margin-top: 1.6em; border-bottom: 1px solid #EFE8DD; padding-bottom: 6px; }
                h3 { font-size: 1.18em; color: #4A3828; margin-top: 1.3em; }
                img { max-width: 100%; height: auto; border-radius: 12px; }
                figure { margin: 20px 0; text-align: center; }
                figcaption { font-size: 13px; color: #6E655D; font-style: italic; margin-top: 6px; }
                table { width: 100%; border-collapse: collapse; margin: 16px 0; }
                th, td { border: 1px solid #E5DDD0; padding: 10px 12px; }
                th { background: #FAF6EE; }
            `,
            images_upload_handler: (blobInfo) => uploadImageToServer(blobInfo.blob(), blobInfo.filename()),
            setup: (editor) => {
                editor.ui.registry.addButton('mayhenMediaPicker', {
                    text: 'Thư viện ảnh',
                    tooltip: 'Chọn ảnh có sẵn từ thư viện Mây Hẹn',
                    onAction: () => openMediaModal('editor'),
                });
                editor.on('init keyup change SetContent', () => {
                    updateWordStats(editor.getContent());
                });
            },
        });
    } else {
        const rawArea = document.getElementById('mayhenAdminNewsContent');
        updateWordStats(rawArea?.value || '');
        rawArea?.addEventListener('input', () => updateWordStats(rawArea.value));
    }

    form.addEventListener('submit', () => {
        if (typeof window.tinymce !== 'undefined') {
            window.tinymce.triggerSave();
        }
    });

    // =========================================================================
    // MEDIA LIBRARY MODAL (PICK FOR COVER OR INSERT INTO TINYMCE)
    // =========================================================================
    const mediaModal = document.getElementById('mayhenMediaModal');
    const mediaGrid = document.getElementById('mayhenMediaGrid');
    const mediaSearch = document.getElementById('mayhenMediaSearch');
    const mediaCloseBtn = document.getElementById('mayhenMediaCloseBtn');
    const mediaModalTitle = document.getElementById('mayhenMediaModalTitle');
    const openCoverLibraryBtn = document.getElementById('mayhenOpenCoverLibraryBtn');
    const openEditorLibraryBtn = document.getElementById('mayhenOpenEditorLibraryBtn');

    let mediaTarget = 'editor';
    let cachedMediaItems = null;

    const renderMediaItems = (items) => {
        if (!mediaGrid) {
            return;
        }
        if (!items.length) {
            mediaGrid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#7A7167;padding:30px 0;">Không tìm thấy ảnh phù hợp trong thư viện.</p>';
            return;
        }
        mediaGrid.innerHTML = items.map((item) => `
            <button type="button" class="mayhen_admin_news_media_item" data-media-url="${item.url}" data-media-name="${item.name}">
                <img src="${item.url}" alt="${item.name}" loading="lazy">
                <span class="mayhen_admin_news_media_name" title="${item.name}">${item.name}</span>
            </button>
        `).join('');
    };

    const loadMediaItems = async () => {
        if (cachedMediaItems !== null) {
            return cachedMediaItems;
        }
        if (!mediaUrl) {
            return [];
        }
        const res = await fetch(mediaUrl, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) {
            return [];
        }
        const data = await res.json();
        cachedMediaItems = Array.isArray(data.items) ? data.items : [];
        return cachedMediaItems;
    };

    const openMediaModal = async (targetMode) => {
        mediaTarget = targetMode;
        if (mediaModalTitle) {
            mediaModalTitle.textContent = targetMode === 'cover'
                ? 'Chọn Ảnh Bìa Từ Thư Viện Ảnh Mây Hẹn'
                : 'Chèn Ảnh Vào Nội Dung Bài Viết';
        }
        mediaModal?.classList.add('is-open');
        if (mediaGrid) {
            mediaGrid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#7A7167;padding:30px 0;">Đang tải thư viện ảnh...</p>';
        }
        const items = await loadMediaItems();
        renderMediaItems(items);
    };

    openCoverLibraryBtn?.addEventListener('click', () => openMediaModal('cover'));
    openEditorLibraryBtn?.addEventListener('click', () => openMediaModal('editor'));
    mediaCloseBtn?.addEventListener('click', () => mediaModal?.classList.remove('is-open'));
    mediaModal?.addEventListener('click', (e) => {
        if (e.target === mediaModal) {
            mediaModal.classList.remove('is-open');
        }
    });

    mediaSearch?.addEventListener('input', async () => {
        const q = mediaSearch.value.trim().toLowerCase();
        const items = await loadMediaItems();
        const filtered = q ? items.filter((i) => i.name.toLowerCase().includes(q)) : items;
        renderMediaItems(filtered);
    });

    mediaGrid?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-media-url]');
        if (!btn) {
            return;
        }
        const url = btn.getAttribute('data-media-url') || '';
        const name = btn.getAttribute('data-media-name') || 'Ảnh minh họa Mây Hẹn';
        if (!url) {
            return;
        }

        if (mediaTarget === 'cover') {
            if (coverUrlHidden) {
                coverUrlHidden.value = url;
            }
            if (coverInput) {
                coverInput.value = '';
            }
            showCoverPreview(url);
        } else {
            const figureHtml = `<figure style="margin:24px 0;"><img src="${url}" alt="${name}" loading="lazy" decoding="async" style="width:100%;height:auto;border-radius:14px;border:1px solid #E5DDD0;"><figcaption style="margin-top:8px;font-size:13px;color:#6E655D;text-align:center;font-style:italic;">Chú thích ảnh minh họa</figcaption></figure><p></p>`;
            const activeEditor = window.tinymce?.get('mayhenAdminNewsContent');
            if (activeEditor) {
                activeEditor.insertContent(figureHtml);
            } else {
                const rawArea = document.getElementById('mayhenAdminNewsContent');
                if (rawArea) {
                    rawArea.value += '\n' + figureHtml;
                }
            }
        }
        mediaModal?.classList.remove('is-open');
    });
});
