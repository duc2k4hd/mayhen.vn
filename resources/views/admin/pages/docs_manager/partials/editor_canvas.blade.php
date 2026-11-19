{{-- 
    MAYHEN.VN — ADMIN DOCS VISUAL WRITER & REALTIME PREVIEW CANVAS
    Trình soạn thảo trực quan (WYSIWYG Visual Writer) kết hợp Xem trước Trực tiếp (Live Preview) giống hệt trang khách hàng.
    Hỗ trợ chỉnh sửa Hình Ảnh (URL, Alt, Caption, Toolbar nổi) & Liên Kết Nội Bộ Thông Minh (Smart Internal Link Picker).
--}}

@push('styles')
{{-- Google Fonts chuẩn trang Docs Mây Hẹn --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/client/docs/css/docs-main.css') }}?v={{ time() }}">

<style>
    /* Bố Cục Trình Soạn Thảo Mây Hẹn Docs Manager */
    .mayhen_editor_suite {
        background: #FFFFFF;
        border: 1px solid var(--mh-admin-border, #E5E7EB);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        position: relative;
    }

    /* Thanh Điều Hướng Chế Độ Soạn Thảo (Mode Tabs) */
    .mayhen_editor_mode_bar {
        background: #F8FAFC;
        border-bottom: 1px solid var(--mh-admin-border, #E5E7EB);
        padding: 10px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .mayhen_editor_mode_tabs {
        display: inline-flex;
        background: #E2E8F0;
        padding: 4px;
        border-radius: 10px;
        gap: 4px;
    }

    .mayhen_editor_mode_btn {
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        border: none;
        background: transparent;
        border-radius: 7px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .mayhen_editor_mode_btn:hover {
        color: #0F172A;
    }

    .mayhen_editor_mode_btn.is-active {
        background: #FFFFFF;
        color: #1E293B;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .mayhen_editor_view_options {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .mayhen_editor_btn_split {
        padding: 6px 12px;
        font-size: 12.5px;
        font-weight: 600;
        color: #334155;
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .mayhen_editor_btn_split.is-active {
        background: #EFF6FF;
        color: #1D4ED8;
        border-color: #93C5FD;
    }

    /* Thanh Công Cụ Định Dạng Thông Minh (Smart Formatting Toolbar) */
    .mayhen_editor_toolbar {
        background: #FFFFFF;
        border-bottom: 1px solid #E2E8F0;
        padding: 8px 14px;
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        user-select: none;
    }

    .mayhen_editor_tb_group {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding-right: 8px;
        margin-right: 4px;
        border-right: 1px solid #E2E8F0;
    }

    .mayhen_editor_tb_btn {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        color: #334155;
        min-width: 32px;
        height: 32px;
        padding: 0 8px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .mayhen_editor_tb_btn:hover {
        background: #F1F5F9;
        color: #0F172A;
        border-color: #CBD5E1;
    }

    .mayhen_editor_tb_btn:active {
        transform: translateY(1px);
    }

    .mayhen_editor_tb_select {
        height: 32px;
        padding: 0 10px;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 6px;
        outline: none;
        cursor: pointer;
    }

    /* Nút Callout Đặc Biệt */
    .mayhen_editor_callout_btn {
        padding: 0 10px;
        font-size: 12px;
        border-radius: 6px;
        border: 1px solid transparent;
        font-weight: 600;
    }
    .mayhen_editor_callout_btn--note {
        background: #EFF6FF;
        color: #1D4ED8;
        border-color: #BFDBFE;
    }
    .mayhen_editor_callout_btn--note:hover {
        background: #DBEAFE;
    }
    .mayhen_editor_callout_btn--tip {
        background: #FEF3C7;
        color: #B45309;
        border-color: #FDE68A;
    }
    .mayhen_editor_callout_btn--tip:hover {
        background: #FDE68A;
    }
    .mayhen_editor_callout_btn--warning {
        background: #FEE2E2;
        color: #B91C1C;
        border-color: #FECACA;
    }
    .mayhen_editor_callout_btn--warning:hover {
        background: #FECACA;
    }

    /* Khung Viết & Xem Trước (Split Canvas) */
    .mayhen_editor_canvas_container {
        display: grid;
        grid-template-columns: 1fr;
        min-height: 580px;
        position: relative;
    }

    .mayhen_editor_canvas_container.is-split-view {
        grid-template-columns: 1fr 1fr;
    }

    .mayhen_editor_pane {
        padding: 24px;
        overflow-y: auto;
        max-height: 800px;
        position: relative;
    }

    .mayhen_editor_pane--source {
        background: #FFFFFF;
        border-right: 1px solid transparent;
    }

    .is-split-view .mayhen_editor_pane--source {
        border-right: 1px solid #E2E8F0;
    }

    /* Trình Soạn Thảo Trực Quan (WYSIWYG Visual Writer Box) */
    .mayhen_editor_visual_box {
        outline: none;
        min-height: 520px;
        font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        font-size: 15.5px;
        line-height: 1.85;
        color: #1F1D1A;
        letter-spacing: -0.005em;
    }

    .mayhen_editor_visual_box[contenteditable="true"]:empty::before {
        content: attr(data-placeholder);
        color: #94A3B8;
        cursor: text;
    }

    /* Phong cách hiển thị nội tuyến bài viết trong Visual Editor */
    .mayhen_editor_visual_box h1,
    .mayhen_editor_visual_box h2,
    .mayhen_editor_visual_box h3 {
        font-family: 'Playfair Display', Georgia, serif;
        color: #181517;
        margin-top: 1.6em;
        margin-bottom: 0.6em;
        line-height: 1.35;
        font-weight: 700;
    }
    .mayhen_editor_visual_box h1 {
        font-size: 30px;
    }
    .mayhen_editor_visual_box h2 {
        font-size: 24px;
        border-bottom: 1px solid #F1EAE5;
        padding-bottom: 8px;
    }
    .mayhen_editor_visual_box h3 {
        font-size: 19px;
    }
    .mayhen_editor_visual_box p {
        margin: 0 0 1.25em 0;
    }
    .mayhen_editor_visual_box blockquote {
        margin: 1.5em 0;
        padding: 14px 20px;
        background: #FDFBF8;
        border-left: 4px solid #C5A880;
        border-radius: 0 8px 8px 0;
        color: #4A453E;
        font-style: italic;
    }
    .mayhen_editor_visual_box ul,
    .mayhen_editor_visual_box ol {
        margin: 1em 0 1.5em 1.5em;
        padding-left: 10px;
    }
    .mayhen_editor_visual_box li {
        margin-bottom: 0.5em;
    }

    /* ĐỊNH DẠNG LINK RÕ RỆT CHUẨN XÁC TRONG VISUAL EDITOR */
    .mayhen_editor_visual_box a {
        color: #1D4ED8 !important;
        text-decoration: underline !important;
        text-decoration-color: #93C5FD !important;
        text-underline-offset: 3px !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        border-radius: 4px !important;
        padding: 1px 4px !important;
        background-color: rgba(239, 246, 255, 0.75) !important;
        transition: all 0.15s ease !important;
        display: inline-block !important;
    }
    .mayhen_editor_visual_box a:hover {
        background-color: #DBEAFE !important;
        color: #1E40AF !important;
        text-decoration-color: #1D4ED8 !important;
    }

    /* Khung Hộp Thông Tin (Callouts) Trực Quan */
    .mayhen_editor_visual_box .mayhen_visual_callout {
        margin: 1.5em 0;
        padding: 16px 18px;
        border-radius: 10px;
        border-left: 4px solid;
    }
    .mayhen_editor_visual_box .mayhen_visual_callout--note {
        background: #F0F7FF;
        border-color: #2563EB;
        color: #1E3A8A;
    }
    .mayhen_editor_visual_box .mayhen_visual_callout--tip {
        background: #FFFBEB;
        border-color: #D97706;
        color: #78350F;
    }
    .mayhen_editor_visual_box .mayhen_visual_callout--warning {
        background: #FEF2F2;
        border-color: #DC2626;
        color: #7F1D1D;
    }
    .mayhen_editor_visual_box .mayhen_visual_callout_header {
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* ĐỊNH DẠNG HÌNH ẢNH TRỰC QUAN & THANH CÔNG CỤ NỔI */
    .mayhen_editor_visual_box .mayhen_visual_figure {
        margin: 1.8em auto;
        text-align: center;
        position: relative;
        display: block;
        max-width: 100%;
        user-select: none;
        border: 2px solid transparent;
        border-radius: 12px;
        padding: 8px;
        transition: all 0.2s ease;
    }
    .mayhen_editor_visual_box .mayhen_visual_figure:hover,
    .mayhen_editor_visual_box .mayhen_visual_figure.is-selected {
        border-color: #3B82F6;
        background: #F8FAFC;
    }
    .mayhen_editor_visual_box .mayhen_visual_figure img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        display: inline-block;
        cursor: pointer;
    }
    .mayhen_editor_visual_box .mayhen_visual_figure figcaption {
        font-size: 13.5px;
        color: #64748B;
        font-style: italic;
        margin-top: 8px;
        padding: 4px 12px;
        border-radius: 6px;
        outline: none;
        display: inline-block;
        min-width: 140px;
        cursor: text;
        transition: background 0.15s, color 0.15s;
    }
    .mayhen_editor_visual_box .mayhen_visual_figure figcaption:hover,
    .mayhen_editor_visual_box .mayhen_visual_figure figcaption:focus {
        background: #FFFFFF;
        box-shadow: 0 0 0 1px #3B82F6;
        color: #1E293B;
    }

    /* Thanh Điều Khiển Nổi Trên Mỗi Ảnh */
    .mayhen_fig_toolbar {
        position: absolute;
        top: 14px;
        right: 14px;
        background: rgba(15, 23, 42, 0.88);
        backdrop-filter: blur(4px);
        padding: 4px 6px;
        border-radius: 8px;
        display: none;
        gap: 6px;
        z-index: 10;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .mayhen_editor_visual_box .mayhen_visual_figure:hover .mayhen_fig_toolbar,
    .mayhen_editor_visual_box .mayhen_visual_figure.is-selected .mayhen_fig_toolbar {
        display: flex;
    }
    .mayhen_fig_btn {
        background: rgba(255, 255, 255, 0.15);
        color: #FFFFFF;
        border: none;
        border-radius: 5px;
        padding: 4px 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.15s;
    }
    .mayhen_fig_btn:hover {
        background: rgba(255, 255, 255, 0.35);
    }
    .mayhen_fig_btn--delete:hover {
        background: #EF4444;
        color: #FFFFFF;
    }

    /* Floating Link Popover (Hộp Điều Khiển Link Khi Click/Focus Vào Link) */
    .mayhen_link_popover {
        position: absolute;
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.15), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        padding: 6px 10px;
        display: none;
        align-items: center;
        gap: 8px;
        z-index: 1000;
        font-size: 13px;
        max-width: 440px;
        pointer-events: auto;
    }
    .mayhen_link_popover.is-active {
        display: flex;
    }
    .mayhen_link_popover_url {
        color: #1D4ED8;
        text-decoration: none;
        max-width: 190px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-weight: 500;
    }
    .mayhen_link_popover_btn {
        padding: 4px 8px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid #E2E8F0;
        background: #F8FAFC;
        color: #334155;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.15s;
    }
    .mayhen_link_popover_btn:hover {
        background: #EEF2F6;
        color: #0F172A;
    }
    .mayhen_link_popover_btn--delete:hover {
        background: #FEE2E2;
        color: #DC2626;
        border-color: #FCA5A5;
    }

    /* Bảng trong Visual Editor */
    .mayhen_editor_visual_box table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5em 0;
        font-size: 14px;
    }
    .mayhen_editor_visual_box th,
    .mayhen_editor_visual_box td {
        border: 1px solid #E2E8F0;
        padding: 10px 12px;
        text-align: left;
    }
    .mayhen_editor_visual_box th {
        background: #F8FAFC;
        font-weight: 700;
    }

    /* Khung Mã Markdown Thô (Markdown Textarea) */
    .mayhen_editor_raw_markdown {
        width: 100%;
        height: 100%;
        min-height: 520px;
        border: none;
        outline: none;
        resize: none;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: 14px;
        line-height: 1.7;
        color: #0F172A;
        background: transparent;
        display: none;
    }

    /* KHUNG LIVE PREVIEW GIỐNG HỆT 100% GIAO DIỆN KHÁCH HÀNG */
    .mayhen_editor_pane--preview {
        background: #FFFDFB;
        padding: 32px;
        position: relative;
    }

    .mayhen_live_preview_wrapper {
        max-width: 760px;
        margin: 0 auto;
        background: #FFFFFF;
        border-radius: 12px;
        padding: 32px 36px;
        border: 1px solid #EFE8DD;
        box-shadow: 0 4px 24px rgba(97, 59, 62, 0.05);
    }

    /* Khung Modal Chèn Đối Tượng */
    .mayhen_editor_modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
    }
    .mayhen_editor_modal.is-open {
        opacity: 1;
        visibility: visible;
    }
    .mayhen_editor_modal_card {
        background: #FFFFFF;
        border-radius: 14px;
        width: 100%;
        max-width: 580px;
        padding: 24px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }
    .mayhen_editor_modal_title {
        font-size: 16px;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
</style>
@endpush

<div class="mayhen_editor_suite" id="mayhenEditorSuite">
    <!-- 1. Thanh Chế Độ Soạn Thảo (Mode Tabs) -->
    <div class="mayhen_editor_mode_bar">
        <div class="mayhen_editor_mode_tabs">
            <button type="button" class="mayhen_editor_mode_btn is-active" id="btnModeVisual" onclick="switchEditorMode('visual')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                <span>Soạn Thảo Trực Quan</span>
            </button>
            <button type="button" class="mayhen_editor_mode_btn" id="btnModePreview" onclick="switchEditorMode('preview')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                <span>Xem Trước Như Khách Hàng</span>
            </button>
            <button type="button" class="mayhen_editor_mode_btn" id="btnModeMarkdown" onclick="switchEditorMode('markdown')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                <span>Mã Markdown</span>
            </button>
        </div>

        <div class="mayhen_editor_view_options">
            <!-- Nút Xem Song Song (Split Screen) -->
            <button type="button" class="mayhen_editor_btn_split" id="btnToggleSplit" onclick="toggleSplitView()" title="Mở song song: Vừa viết bên trái vừa xem kết quả bài đọc bên phải">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="3" x2="12" y2="21"/></svg>
                <span>Chia Đôi Màn Hình</span>
            </button>

            <!-- Mẫu Khung Bài Viết Có Sẵn (Quick Templates) -->
            <select class="mayhen_editor_tb_select" onchange="applyQuickTemplate(this.value); this.selectedIndex = 0;" style="background: #F8FAFC; border-color: #CBD5E1;">
                <option value="">📑 Chọn Mẫu Bài Viết Có Sẵn...</option>
                <option value="guide">1. Mẫu Hướng Dẫn Từng Bước (1-2-3)</option>
                <option value="feature">2. Mẫu Giới Thiệu Tính Năng Mới</option>
                <option value="sop">3. Mẫu Sổ Tay SOP Vận Hành</option>
                <option value="faq">4. Mẫu Câu Hỏi Thường Gặp (FAQ)</option>
            </select>
        </div>
    </div>

    <!-- 2. Thanh Công Cụ Định Dạng Thông Minh (Smart Toolbar) -->
    <div class="mayhen_editor_toolbar" id="mayhenEditorToolbar">
        <!-- Nhóm Tiêu đề -->
        <div class="mayhen_editor_tb_group">
            <select class="mayhen_editor_tb_select" onchange="executeHeading(this.value); this.selectedIndex = 0;" title="Đổi cấp độ tiêu đề">
                <option value="">Kiểu Chữ...</option>
                <option value="p">Văn bản thường</option>
                <option value="h2">Tiêu đề lớn (H2 ##)</option>
                <option value="h3">Tiêu đề con (H3 ###)</option>
            </select>
        </div>

        <!-- Nhóm In đậm, In nghiêng, Gạch chân -->
        <div class="mayhen_editor_tb_group">
            <button type="button" class="mayhen_editor_tb_btn" onclick="execCmd('bold')" title="In Đậm (Ctrl+B)"><b>B</b></button>
            <button type="button" class="mayhen_editor_tb_btn" onclick="execCmd('italic')" title="In Nghiêng (Ctrl+I)"><i>I</i></button>
            <button type="button" class="mayhen_editor_tb_btn" onclick="execCmd('underline')" title="Gạch Chân (Ctrl+U)"><u>U</u></button>
        </div>

        <!-- Nhóm Hộp Thông Tin (Callouts) -->
        <div class="mayhen_editor_tb_group">
            <button type="button" class="mayhen_editor_tb_btn mayhen_editor_callout_btn mayhen_editor_callout_btn--note" onclick="insertVisualCallout('note')" title="Chèn Hộp Ghi Chú">
                📘 Ghi chú
            </button>
            <button type="button" class="mayhen_editor_tb_btn mayhen_editor_callout_btn mayhen_editor_callout_btn--tip" onclick="insertVisualCallout('tip')" title="Chèn Hộp Mẹo Hay">
                💡 Mẹo hay
            </button>
            <button type="button" class="mayhen_editor_tb_btn mayhen_editor_callout_btn mayhen_editor_callout_btn--warning" onclick="insertVisualCallout('warning')" title="Chèn Hộp Cảnh Báo">
                ⚠️ Cảnh báo
            </button>
        </div>

        <!-- Nhóm Danh Sách -->
        <div class="mayhen_editor_tb_group">
            <button type="button" class="mayhen_editor_tb_btn" onclick="execCmd('insertUnorderedList')" title="Danh Sách Chấm Tròn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            </button>
            <button type="button" class="mayhen_editor_tb_btn" onclick="execCmd('insertOrderedList')" title="Danh Sách Số Thứ Tự">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/></svg>
            </button>
        </div>

        <!-- Nhóm Đối Tượng: Ảnh, Link, Bảng, Trích Dẫn -->
        <div class="mayhen_editor_tb_group">
            <button type="button" class="mayhen_editor_tb_btn" onclick="openInsertImageModal()" title="Chèn Ảnh Minh Họa Kèm Chú Thích">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <span>Ảnh</span>
            </button>
            <button type="button" class="mayhen_editor_tb_btn" onclick="openInsertLinkModal()" title="Chèn Đường Dẫn Liên Kết">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                <span>Link</span>
            </button>
            <button type="button" class="mayhen_editor_tb_btn" onclick="insertVisualTable()" title="Chèn Bảng Dữ Liệu">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
                <span>Bảng</span>
            </button>
            <button type="button" class="mayhen_editor_tb_btn" onclick="insertVisualQuote()" title="Trích Dẫn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/></svg>
            </button>
        </div>

        <!-- Nhóm Tẩy Định Dạng -->
        <button type="button" class="mayhen_editor_tb_btn" onclick="execCmd('removeFormat')" title="Xóa bỏ định dạng đoạn đã chọn">
            🧹 Xóa định dạng
        </button>
    </div>

    <!-- 3. Khu Vực Soạn Thảo & Xem Trước (Canvas) -->
    <div class="mayhen_editor_canvas_container" id="editorCanvasContainer">
        <!-- Vùng 1: Soạn Thảo (Visual Writer & Raw Markdown) -->
        <div class="mayhen_editor_pane mayhen_editor_pane--source" id="editorSourcePane">
            <!-- Box Soạn Thảo Trực Quan (WYSIWYG Visual Writer) -->
            <div class="mayhen_editor_visual_box" id="visualEditorBox" contenteditable="true" data-placeholder="Bắt đầu viết bài viết tài liệu của bạn tại đây như trong Word/Notion... Bấm các nút công cụ phía trên để chèn Tiêu đề, In đậm, Hộp Ghi chú, Ảnh minh họa, Link liên kết..."></div>

            <!-- Box Soạn Thảo Markdown Thô (Raw Code Textarea) -->
            <textarea class="mayhen_editor_raw_markdown" id="rawMarkdownTextarea" name="content" placeholder="Soạn thảo bằng cú pháp Markdown...">{!! old('content', $article['content'] ?? '') !!}</textarea>
        </div>

        <!-- Vùng 2: Xem Trước Giống Hệt 100% Khách Hàng (Live Preview) -->
        <div class="mayhen_editor_pane mayhen_editor_pane--preview" id="editorPreviewPane" style="display: none;">
            <div class="mayhen_live_preview_wrapper">
                <!-- Breadcrumb Preview -->
                <div style="font-size: 13px; color: #8A7E72; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                    <span>Tổng quan</span>
                    <span>/</span>
                    <span id="previewCategoryName" style="color: #613B3E; font-weight: 600;">Khởi Đầu Nhanh</span>
                    <span>/</span>
                    <span id="previewBreadcrumbTitle" style="color: #1F1D1A; font-weight: 500;">Tiêu đề tài liệu</span>
                </div>

                @php
                    $docsEditorBrandName = \App\Models\PlatformSetting::getBrandName();
                    $docsEditorBrandNameUpper = \App\Models\PlatformSetting::getBrandNameUpper();
                    $docsEditorBrandMonogram = mb_strtoupper(mb_substr(trim($docsEditorBrandName), 0, 1, 'UTF-8'), 'UTF-8') ?: 'M';
                @endphp
                <!-- Kicker & Reading Time -->
                <div style="font-size: 12px; font-weight: 700; letter-spacing: 0.12em; color: #805052; text-transform: uppercase; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 24px; height: 1px; background: #C5A880;"></span>
                    <span id="previewKickerText">HƯỚNG DẪN {{ $docsEditorBrandNameUpper }}</span>
                    <span>·</span>
                    <span>5 PHÚT ĐỌC</span>
                </div>

                <!-- Tiêu đề lớn H1 (Playfair Display) -->
                <h1 id="previewMainHeading" style="font-family: 'Playfair Display', Georgia, serif; font-size: 32px; font-weight: 700; color: #181517; line-height: 1.3; margin: 0 0 16px 0;">
                    Tiêu đề tài liệu
                </h1>

                <!-- Lead Text (Tóm tắt) -->
                <p id="previewLeadSummary" style="font-size: 16.5px; line-height: 1.7; color: #5A524A; font-style: italic; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #F1EAE5;">
                    Tóm tắt ngắn về bài viết...
                </p>

                <!-- Meta Tác giả & Ngày cập nhật -->
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 28px;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: #613B3E; color: #FFF; display: flex; align-items: center; justify-content: center; font-family: 'Playfair Display', serif; font-size: 18px; font-weight: 700;">
                        {{ $docsEditorBrandMonogram }}
                    </div>
                    <div>
                        <strong style="display: block; font-size: 13.5px; color: #1F1D1A;">Đội ngũ {{ $docsEditorBrandName }}</strong>
                        <small style="color: #8A7E72; font-size: 12px;">Cập nhật: <span id="previewUpdatedAt">{{ date('d/m/Y') }}</span></small>
                    </div>
                </div>

                <!-- Thân Bài Viết Rendered HTML -->
                <div class="mayhen_docs_detail_article_body" id="previewRenderedBody">
                    <p>Nội dung bài viết sẽ hiển thị tại đây...</p>
                </div>

                <!-- Footer Box Hỗ Trợ Quiet Luxury -->
                <div style="margin-top: 40px; padding: 22px; background: #FDFBF8; border: 1px solid #EFE8DD; border-radius: 12px; text-align: center;">
                    <div style="font-size: 18px; color: #C5A880; margin-bottom: 6px;">✿</div>
                    <strong style="font-size: 14px; color: #1F1D1A; display: block; margin-bottom: 4px;">Bạn cần thêm một chút trợ giúp?</strong>
                    <p style="font-size: 13px; color: #7A726A; margin: 0;">{{ $docsEditorBrandName }} luôn đồng hành để ngày trọng đại của hai bạn diễn ra trọn vẹn và nhẹ nhàng nhất.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Floating Link Popover (Hộp Điều Khiển Link Thông Minh) -->
    <div class="mayhen_link_popover" id="floatingLinkPopover">
        <span style="font-size: 14px;">🔗</span>
        <a href="#" target="_blank" rel="noopener" class="mayhen_link_popover_url" id="popoverLinkUrlText" title="Mở đường dẫn này trong tab mới">https://...</a>
        <button type="button" class="mayhen_link_popover_btn" onclick="editCurrentActiveLink()">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            <span>Sửa</span>
        </button>
        <button type="button" class="mayhen_link_popover_btn" onclick="openCurrentActiveLink()">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            <span>Mở</span>
        </button>
        <button type="button" class="mayhen_link_popover_btn mayhen_link_popover_btn--delete" onclick="unlinkCurrentActiveLink()" title="Gỡ bỏ liên kết, giữ nguyên văn bản">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            <span>Gỡ</span>
        </button>
    </div>
</div>

<!-- Modal 1: Chèn & Chỉnh Sửa Hình Ảnh Minh Họa (Có Thư Viện Ảnh Docs & Preview) -->
<div class="mayhen_editor_modal" id="modalInsertImage">
    <div class="mayhen_editor_modal_card">
        <div class="mayhen_editor_modal_title">
            <span id="modalImageHeadingTitle">🖼️ Chèn / Chỉnh Sửa Hình Ảnh Minh Họa</span>
            <button type="button" onclick="closeModal('modalInsertImage')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #64748B;">&times;</button>
        </div>

        <!-- Bộ Chọn Ảnh Có Sẵn Trong Hệ Thống Docs -->
        <div style="margin-bottom: 14px; background: #F8FAFC; border: 1px dashed #CBD5E1; border-radius: 8px; padding: 12px;">
            <label style="display: block; font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 6px;">
                📁 Chọn Nhanh Ảnh Có Sẵn Từ Hệ Thống Docs
            </label>
            <select id="selectDocsAvailableImage" class="mayhen_admin_select" onchange="onSelectDocsImageChanged(this.value)">
                <option value="">-- Chọn ảnh docs có sẵn trong thư mục assets --</option>
                @if(isset($docsImages) && count($docsImages) > 0)
                    @php
                        $groupedImages = collect($docsImages)->groupBy(function($item) {
                            return str_contains($item, '/admin/') ? 'Ảnh Tài Liệu Admin SOP' : 'Ảnh Tài Liệu Khách Hàng (Client)';
                        });
                    @endphp
                    @foreach($groupedImages as $grpName => $imgs)
                        <optgroup label="{{ $grpName }}">
                            @foreach($imgs as $imgItem)
                                <option value="{{ $imgItem }}">{{ basename($imgItem) }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                @endif
            </select>
            <small style="color: #64748B; font-size: 11.5px; display: block; margin-top: 4px;">Hệ thống tự động quét tất cả ảnh trong thư viện docs của {{ $docsEditorBrandName }}.</small>
        </div>

        <div style="margin-bottom: 14px;">
            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Đường Dẫn Hình Ảnh (URL) *</label>
            <input type="text" id="inputImageUrl" class="mayhen_admin_input" placeholder="Ví dụ: /assets/client/docs/images/client/01-getting-started/cli-start-001-step-1.png" oninput="updateImagePreviewBox(this.value)">
        </div>

        <!-- Khung Xem Trước Hình Ảnh Thu Nhỏ (Mini Image Preview) -->
        <div id="imagePreviewContainer" style="margin-bottom: 14px; text-align: center; background: #F1F5F9; border-radius: 8px; padding: 10px; display: none;">
            <span style="font-size: 12px; font-weight: 600; color: #64748B; display: block; margin-bottom: 6px;">Xem trước hình ảnh:</span>
            <img id="imagePreviewThumbnail" src="" alt="Preview" style="max-height: 140px; max-width: 100%; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid #CBD5E1;">
        </div>

        <div style="margin-bottom: 14px;">
            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Văn Bản Thay Thế (Alt Text cho SEO & Trợ năng)</label>
            <input type="text" id="inputImageAlt" class="mayhen_admin_input" placeholder="Ví dụ: Giao diện Trang chủ {{ $docsEditorBrandName }} phong cách Quiet Luxury">
        </div>

        <div style="margin-bottom: 18px;">
            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Chú Thích Ảnh (Caption hiển thị dưới ảnh) *</label>
            <input type="text" id="inputImageCaption" class="mayhen_admin_input" placeholder="Ví dụ: Giao diện Trang chủ {{ $docsEditorBrandName }}: Tôn vinh nét đẹp thanh lịch và trải nghiệm cảm xúc trọn vẹn.">
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="mayhen_admin_btn_cancel" style="width: auto; margin-top: 0;" onclick="closeModal('modalInsertImage')">Hủy Bỏ</button>
            <button type="button" class="mayhen_admin_btn_primary" id="btnConfirmImageModal" onclick="confirmInsertOrUpdateImage()">Lưu Hình Ảnh</button>
        </div>
    </div>
</div>

<!-- Modal 2: Chèn & Chỉnh Sửa Liên Kết (Có Smart Internal Link Picker) -->
<div class="mayhen_editor_modal" id="modalInsertLink">
    <div class="mayhen_editor_modal_card">
        <div class="mayhen_editor_modal_title">
            <span id="modalLinkHeadingTitle">🔗 Chèn / Chỉnh Sửa Đường Dẫn Liên Kết</span>
            <button type="button" onclick="closeModal('modalInsertLink')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #64748B;">&times;</button>
        </div>

        <!-- Bộ Chọn Nhanh Bài Viết Nội Bộ Docs Thông Minh -->
        <div style="margin-bottom: 16px; background: #EFF6FF; border: 1px dashed #93C5FD; border-radius: 8px; padding: 12px;">
            <label style="display: block; font-size: 13px; font-weight: 700; color: #1E40AF; margin-bottom: 6px;">
                📑 Hoặc Chọn Nhanh Bài Viết Nội Bộ Trong Hệ Thống
            </label>
            <select id="selectInternalDocLink" class="mayhen_admin_select" onchange="onSelectInternalDocChanged(this)">
                <option value="">-- Chọn bài viết để tự động điền Tiêu đề và URL chuẩn --</option>
                @if(isset($allArticles) && count($allArticles) > 0)
                    @php
                        $groupedArticles = collect($allArticles)->groupBy(function($item) {
                            return ($item['space'] === 'admin' ? '[ADMIN] ' : '[CLIENT] ') . ($item['category_name'] ?? 'Chuyên mục khác');
                        });
                    @endphp
                    @foreach($groupedArticles as $groupName => $items)
                        <optgroup label="{{ $groupName }}">
                            @foreach($items as $artItem)
                                @php
                                    $artRelUrl = ($artItem['space'] === 'admin')
                                        ? '/admin/docs/' . $artItem['category_slug'] . '/' . $artItem['id']
                                        : '/docs/' . $artItem['category_slug'] . '/' . $artItem['id'];
                                @endphp
                                <option value="{{ $artRelUrl }}" data-title="{{ $artItem['title'] ?? '' }}">
                                    [{{ $artItem['id'] }}] {{ Str::limit($artItem['title'] ?? '', 50) }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                @endif
            </select>
            <small style="color: #3B82F6; font-size: 11.5px; display: block; margin-top: 4px;">Người mới không cần nhớ hay gõ tay URL, hệ thống sẽ tự động liên kết chuẩn xác.</small>
        </div>

        <div style="margin-bottom: 14px;">
            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Văn Bản Hiển Thị (Anchor Text) *</label>
            <input type="text" id="inputLinkText" class="mayhen_admin_input" placeholder="Ví dụ: Hướng dẫn đăng ký tài khoản & khảo sát Onboarding 4 bước">
        </div>

        <div style="margin-bottom: 18px;">
            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Địa Chỉ Web Đích (URL) *</label>
            <input type="text" id="inputLinkUrl" class="mayhen_admin_input" placeholder="Ví dụ: /docs/getting-started/CLI-START-002 hoặc https://mayhen.vn">
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="mayhen_admin_btn_cancel" style="width: auto; margin-top: 0;" onclick="closeModal('modalInsertLink')">Hủy Bỏ</button>
            <button type="button" class="mayhen_admin_btn_primary" id="btnConfirmLinkModal" onclick="confirmInsertOrUpdateLink()">Lưu Liên Kết</button>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    var currentMode = 'visual'; // 'visual', 'preview', 'markdown'
    var isSplit = false;
    var previewDebounceTimer = null;

    var visualBox = document.getElementById('visualEditorBox');
    var rawTextarea = document.getElementById('rawMarkdownTextarea');
    var sourcePane = document.getElementById('editorSourcePane');
    var previewPane = document.getElementById('editorPreviewPane');
    var container = document.getElementById('editorCanvasContainer');
    var floatingPopover = document.getElementById('floatingLinkPopover');

    // Biến lưu trữ đối tượng đang được chỉnh sửa trực tiếp
    var activeFigureTarget = null;
    var activeLinkTarget = null;
    var savedSelectionRange = null;

    function saveSelection() {
        var sel = window.getSelection();
        if (sel.rangeCount > 0) {
            savedSelectionRange = sel.getRangeAt(0);
        }
    }

    function restoreSelection() {
        if (savedSelectionRange) {
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(savedSelectionRange);
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function escapeAttr(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    // ----------------------------------------------------
    // 1. Chuyển đổi Markdown sang HTML chuẩn xác phía Client
    // ----------------------------------------------------
    function markdownToHtml(md) {
        if (!md) return '';
        var lines = md.replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n');
        var html = '';
        var inList = false;

        for (var i = 0; i < lines.length; i++) {
            var line = lines[i];
            var trimmed = line.trim();

            // Heading 1 (# ...)
            if (trimmed.startsWith('# ')) {
                if (inList) { html += '</ul>'; inList = false; }
                html += '<h1>' + escapeInline(trimmed.substring(2)) + '</h1>';
                continue;
            }
            // Heading 2 (## ...)
            if (trimmed.startsWith('## ')) {
                if (inList) { html += '</ul>'; inList = false; }
                html += '<h2>' + escapeInline(trimmed.substring(3)) + '</h2>';
                continue;
            }
            // Heading 3 (### ...)
            if (trimmed.startsWith('### ')) {
                if (inList) { html += '</ul>'; inList = false; }
                html += '<h3>' + escapeInline(trimmed.substring(4)) + '</h3>';
                continue;
            }

            // Xử lý Hình Ảnh: ![alt](src) kèm dòng chú thích *caption* ngay kế tiếp
            var imgMatch = trimmed.match(/^!\[(.*?)\]\((.*?)\)$/);
            if (imgMatch) {
                if (inList) { html += '</ul>'; inList = false; }
                var alt = imgMatch[1];
                var src = imgMatch[2];
                var caption = alt;

                // Kiểm tra xem dòng tiếp theo có phải chú thích *caption* hay _caption_ không
                if (i + 1 < lines.length) {
                    var nextLine = lines[i + 1].trim();
                    var capMatch = nextLine.match(/^\*(.*?)\*$/) || nextLine.match(/^_(.*?)_$/);
                    if (capMatch) {
                        caption = capMatch[1];
                        i++; // Nhảy qua dòng caption này vì đã gộp vào thẻ figure
                    }
                }

                html += '<figure class="mayhen_visual_figure" data-alt="' + escapeAttr(alt) + '">' +
                    '<div class="mayhen_fig_toolbar">' +
                        '<button type="button" class="mayhen_fig_btn" onclick="window.editActiveFigure(this)">✏️ Sửa ảnh</button>' +
                        '<button type="button" class="mayhen_fig_btn mayhen_fig_btn--delete" onclick="window.deleteActiveFigure(this)">🗑️ Xóa</button>' +
                    '</div>' +
                    '<img src="' + escapeAttr(src) + '" alt="' + escapeAttr(alt) + '" loading="lazy">' +
                    '<figcaption contenteditable="true" data-placeholder="Nhập chú thích ảnh...">' + escapeHtml(caption) + '</figcaption>' +
                    '</figure>';
                continue;
            }

            // Callout Box (> [!NOTE], > [!TIP], > [!WARNING])
            if (trimmed.startsWith('> [!NOTE]') || trimmed.startsWith('> [!TIP]') || trimmed.startsWith('> [!WARNING]')) {
                if (inList) { html += '</ul>'; inList = false; }
                var type = trimmed.indexOf('NOTE') !== -1 ? 'note' : (trimmed.indexOf('TIP') !== -1 ? 'tip' : 'warning');
                var title = type === 'note' ? '📘 GHI CHÚ' : (type === 'tip' ? '💡 MẸO HAY' : '⚠️ CẢNH BÁO');
                var calloutContent = '';
                i++;
                while (i < lines.length && lines[i].trim().startsWith('>')) {
                    var qLine = lines[i].trim().replace(/^>\s?/, '');
                    if (qLine) calloutContent += '<p>' + escapeInline(qLine) + '</p>';
                    i++;
                }
                i--;
                html += '<div class="mayhen_visual_callout mayhen_visual_callout--' + type + '"><div class="mayhen_visual_callout_header">' + title + '</div>' + (calloutContent || '<p>Nội dung ghi chú...</p>') + '</div>';
                continue;
            }

            // Trích dẫn thường (> ...)
            if (trimmed.startsWith('>')) {
                if (inList) { html += '</ul>'; inList = false; }
                var quoteText = trimmed.replace(/^>\s?/, '');
                html += '<blockquote>' + escapeInline(quoteText) + '</blockquote>';
                continue;
            }

            // Bullet List (- ...)
            if (trimmed.startsWith('- ') || trimmed.startsWith('* ')) {
                if (!inList) {
                    html += '<ul>';
                    inList = true;
                }
                html += '<li>' + escapeInline(trimmed.substring(2)) + '</li>';
                continue;
            } else if (inList) {
                html += '</ul>';
                inList = false;
            }

            // Bảng Markdown đơn giản (| col 1 | col 2 |)
            if (trimmed.startsWith('|') && trimmed.endsWith('|')) {
                var tableRows = [];
                while (i < lines.length && lines[i].trim().startsWith('|') && lines[i].trim().endsWith('|')) {
                    tableRows.push(lines[i].trim());
                    i++;
                }
                i--;
                if (tableRows.length >= 2) {
                    html += '<table>';
                    for (var r = 0; r < tableRows.length; r++) {
                        var cols = tableRows[r].split('|').slice(1, -1);
                        if (tableRows[r].indexOf('---') !== -1) continue;
                        var tag = r === 0 ? 'th' : 'td';
                        html += '<tr>';
                        for (var c = 0; c < cols.length; c++) {
                            html += '<' + tag + '>' + escapeInline(cols[c].trim()) + '</' + tag + '>';
                        }
                        html += '</tr>';
                    }
                    html += '</table>';
                }
                continue;
            }

            // Đoạn văn thường
            if (trimmed !== '') {
                html += '<p>' + escapeInline(trimmed) + '</p>';
            }
        }

        if (inList) html += '</ul>';
        return html;
    }

    function escapeInline(text) {
        if (!text) return '';
        // Bold: **text**
        text = text.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
        // Italic: *text*
        text = text.replace(/\*(.*?)\*/g, '<i>$1</i>');
        // Code: `code`
        text = text.replace(/`([^`]+)`/g, '<code>$1</code>');
        // Link: [text](url) -> thẻ a có class và data chuẩn
        text = text.replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');
        return text;
    }

    // ----------------------------------------------------
    // 2. Chuyển đổi HTML của Visual Editor sang Markdown
    // ----------------------------------------------------
    function htmlToMarkdown(html) {
        if (!html) return '';
        var temp = document.createElement('div');
        temp.innerHTML = html;

        function walk(node) {
            if (node.nodeType === Node.TEXT_NODE) {
                return node.nodeValue;
            }
            if (node.nodeType !== Node.ELEMENT_NODE) return '';

            // Bỏ qua thanh công cụ của figure khi serialize sang Markdown
            if (node.classList.contains('mayhen_fig_toolbar') || node.classList.contains('mayhen_link_popover')) {
                return '';
            }

            var tag = node.tagName.toLowerCase();
            var inner = '';
            for (var c = 0; c < node.childNodes.length; c++) {
                inner += walk(node.childNodes[c]);
            }

            switch (tag) {
                case 'h1':
                    return '# ' + inner.trim() + '\n\n';
                case 'h2':
                    return '## ' + inner.trim() + '\n\n';
                case 'h3':
                    return '### ' + inner.trim() + '\n\n';
                case 'b':
                case 'strong':
                    return '**' + inner.trim() + '**';
                case 'i':
                case 'em':
                    return '*' + inner.trim() + '*';
                case 'u':
                    return inner;
                case 'code':
                    return '`' + inner.trim() + '`';
                case 'p':
                    return inner.trim() ? (inner.trim() + '\n\n') : '\n';
                case 'blockquote':
                    return '> ' + inner.trim().replace(/\n/g, '\n> ') + '\n\n';
                case 'ul':
                    var ulMd = '';
                    node.querySelectorAll(':scope > li').forEach(function(li) {
                        ulMd += '- ' + walk(li).trim() + '\n';
                    });
                    return ulMd + '\n';
                case 'ol':
                    var olMd = '';
                    var idx = 1;
                    node.querySelectorAll(':scope > li').forEach(function(li) {
                        olMd += (idx++) + '. ' + walk(li).trim() + '\n';
                    });
                    return olMd + '\n';
                case 'a':
                    var href = node.getAttribute('href') || '#';
                    return '[' + inner.trim() + '](' + href + ')';
                case 'img':
                    var alt = node.getAttribute('alt') || ('Ảnh minh họa ' + @json($docsEditorBrandName));
                    var src = node.getAttribute('src') || '';
                    return '![' + alt + '](' + src + ')\n\n';
                case 'figure':
                    var img = node.querySelector('img');
                    var captionEl = node.querySelector('figcaption');
                    if (img) {
                        var imgSrc = img.getAttribute('src') || '';
                        var imgAlt = img.getAttribute('alt') || (captionEl ? captionEl.innerText.trim() : 'Ảnh minh họa');
                        var capText = captionEl ? captionEl.innerText.trim() : '';
                        if (capText && capText !== '') {
                            return '![' + imgAlt + '](' + imgSrc + ')\n*' + capText + '*\n\n';
                        } else {
                            return '![' + imgAlt + '](' + imgSrc + ')\n\n';
                        }
                    }
                    return inner;
                case 'div':
                    if (node.classList.contains('mayhen_visual_callout--note')) {
                        return '> [!NOTE]\n> ' + inner.trim().replace(/\n/g, '\n> ') + '\n\n';
                    }
                    if (node.classList.contains('mayhen_visual_callout--tip')) {
                        return '> [!TIP]\n> ' + inner.trim().replace(/\n/g, '\n> ') + '\n\n';
                    }
                    if (node.classList.contains('mayhen_visual_callout--warning')) {
                        return '> [!WARNING]\n> ' + inner.trim().replace(/\n/g, '\n> ') + '\n\n';
                    }
                    return inner + '\n';
                case 'table':
                    var tableMd = '\n';
                    var rows = node.querySelectorAll('tr');
                    rows.forEach(function(tr, rIdx) {
                        var cells = tr.querySelectorAll('th, td');
                        var rowStr = '|';
                        cells.forEach(function(td) {
                            rowStr += ' ' + walk(td).trim() + ' |';
                        });
                        tableMd += rowStr + '\n';
                        if (rIdx === 0) {
                            var sep = '|';
                            cells.forEach(function() {
                                sep += ' :--- |';
                            });
                            tableMd += sep + '\n';
                        }
                    });
                    return tableMd + '\n';
                default:
                    return inner;
            }
        }

        var md = walk(temp);
        return md.replace(/\n{3,}/g, '\n\n').trim();
    }

    // ----------------------------------------------------
    // 3. Đồng Bộ Dữ Liệu và Live Preview Server
    // ----------------------------------------------------
    function syncContent(from) {
        if (from === 'visual') {
            rawTextarea.value = htmlToMarkdown(visualBox.innerHTML);
        } else if (from === 'markdown') {
            visualBox.innerHTML = markdownToHtml(rawTextarea.value);
            attachVisualEditorInteractions();
        }
        triggerLivePreview();
    }

    function triggerLivePreview() {
        if (previewDebounceTimer) clearTimeout(previewDebounceTimer);
        previewDebounceTimer = setTimeout(function() {
            renderLivePreview();
        }, 300);
    }

    function renderLivePreview() {
        var titleInput = document.getElementById('doc_title');
        var summaryInput = document.getElementById('doc_summary');
        var catNameInput = document.getElementById('category_name');
        var spaceRadio = document.querySelector('input[name="space"]:checked');

        var titleVal = (titleInput ? titleInput.value : '') || 'Tiêu đề tài liệu';
        var summaryVal = summaryInput ? summaryInput.value : '';
        var catVal = (catNameInput ? catNameInput.value : '') || 'Chuyên mục';
        var spaceVal = spaceRadio ? spaceRadio.value : 'client';
        var mdContent = rawTextarea.value;

        var previewTitle = document.getElementById('previewMainHeading');
        var previewBread = document.getElementById('previewBreadcrumbTitle');
        var previewCat = document.getElementById('previewCategoryName');
        var previewKicker = document.getElementById('previewKickerText');
        var previewSummary = document.getElementById('previewLeadSummary');

        if (previewTitle) previewTitle.innerText = titleVal;
        if (previewBread) previewBread.innerText = titleVal;
        if (previewCat) previewCat.innerText = catVal;
        if (previewKicker) previewKicker.innerText = catVal.toUpperCase();
        if (previewSummary) {
            previewSummary.innerText = summaryVal || 'Tóm tắt ngắn gọn về tài liệu này...';
            previewSummary.style.display = summaryVal ? 'block' : 'none';
        }

        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch("{{ route('admin.docs_manager.preview') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                content: mdContent,
                title: titleVal,
                summary: summaryVal,
                category_name: catVal,
                space: spaceVal
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data && data.success) {
                var bodyEl = document.getElementById('previewRenderedBody');
                if (bodyEl) {
                    bodyEl.innerHTML = data.html_body || '<p>Chưa có nội dung...</p>';
                }
            }
        })
        .catch(function() {
            var bodyEl = document.getElementById('previewRenderedBody');
            if (bodyEl) {
                bodyEl.innerHTML = markdownToHtml(mdContent) || '<p>Chưa có nội dung...</p>';
            }
        });
    }

    // ----------------------------------------------------
    // 4. Quản Lý Floating Popover & Tương Tác Visual Editor
    // ----------------------------------------------------
    function hideFloatingPopover() {
        if (floatingPopover) {
            floatingPopover.classList.remove('is-active');
            floatingPopover.style.display = 'none';
        }
    }

    function showFloatingLinkPopover(targetAnchor) {
        activeLinkTarget = targetAnchor;
        var href = targetAnchor.getAttribute('href') || '#';
        var urlTextEl = document.getElementById('popoverLinkUrlText');
        if (urlTextEl) {
            urlTextEl.innerText = href;
            urlTextEl.setAttribute('href', href);
        }

        var rect = targetAnchor.getBoundingClientRect();
        var suiteRect = document.getElementById('mayhenEditorSuite').getBoundingClientRect();

        var topPos = (rect.bottom - suiteRect.top) + 6;
        var leftPos = (rect.left - suiteRect.left);

        // Giữ popover không tràn màn hình bên phải
        if (leftPos + 400 > suiteRect.width) {
            leftPos = suiteRect.width - 410;
        }
        if (leftPos < 10) leftPos = 10;

        floatingPopover.style.top = topPos + 'px';
        floatingPopover.style.left = leftPos + 'px';
        floatingPopover.style.display = 'flex';
        floatingPopover.classList.add('is-active');
    }

    window.openCurrentActiveLink = function() {
        if (activeLinkTarget) {
            var href = activeLinkTarget.getAttribute('href');
            if (href && href !== '#') {
                window.open(href, '_blank', 'noopener,noreferrer');
            }
        }
    };

    window.unlinkCurrentActiveLink = function() {
        if (activeLinkTarget) {
            var text = activeLinkTarget.innerText;
            var textNode = document.createTextNode(text);
            activeLinkTarget.parentNode.replaceChild(textNode, activeLinkTarget);
            activeLinkTarget = null;
            hideFloatingPopover();
            syncContent('visual');
        }
    };

    window.editCurrentActiveLink = function() {
        if (activeLinkTarget) {
            var text = activeLinkTarget.innerText;
            var href = activeLinkTarget.getAttribute('href') || '';
            hideFloatingPopover();
            openInsertLinkModal(true, text, href);
        }
    };

    // Tương tác Hình Ảnh (Figure Toolbar)
    window.editActiveFigure = function(btn) {
        var figure = btn.closest('.mayhen_visual_figure');
        if (!figure) return;
        activeFigureTarget = figure;

        var img = figure.querySelector('img');
        var capEl = figure.querySelector('figcaption');

        var currentUrl = img ? (img.getAttribute('src') || '') : '';
        var currentAlt = img ? (img.getAttribute('alt') || '') : '';
        var currentCap = capEl ? capEl.innerText.trim() : currentAlt;

        openInsertImageModal(true, currentUrl, currentAlt, currentCap);
    };

    window.deleteActiveFigure = function(btn) {
        var figure = btn.closest('.mayhen_visual_figure');
        if (figure) {
            if (confirm('Bạn có chắc chắn muốn xóa hình ảnh này khỏi bài viết?')) {
                figure.remove();
                activeFigureTarget = null;
                syncContent('visual');
            }
        }
    };

    function attachVisualEditorInteractions() {
        // Đính kèm thanh công cụ vào các figure nếu chưa có
        var figures = visualBox.querySelectorAll('.mayhen_visual_figure');
        figures.forEach(function(fig) {
            if (!fig.querySelector('.mayhen_fig_toolbar')) {
                var tb = document.createElement('div');
                tb.className = 'mayhen_fig_toolbar';
                tb.innerHTML = '<button type="button" class="mayhen_fig_btn" onclick="window.editActiveFigure(this)">✏️ Sửa ảnh</button>' +
                    '<button type="button" class="mayhen_fig_btn mayhen_fig_btn--delete" onclick="window.deleteActiveFigure(this)">🗑️ Xóa</button>';
                fig.prepend(tb);
            }
            var figCap = fig.querySelector('figcaption');
            if (figCap) {
                figCap.setAttribute('contenteditable', 'true');
                if (!figCap.hasAttribute('data-placeholder')) {
                    figCap.setAttribute('data-placeholder', 'Nhập chú thích ảnh...');
                }
            }
        });
    }

    // ----------------------------------------------------
    // 5. Chuyển Đổi Tab & View Mode
    // ----------------------------------------------------
    window.switchEditorMode = function(mode) {
        currentMode = mode;
        var btnV = document.getElementById('btnModeVisual');
        var btnP = document.getElementById('btnModePreview');
        var btnM = document.getElementById('btnModeMarkdown');
        var toolbar = document.getElementById('mayhenEditorToolbar');

        [btnV, btnP, btnM].forEach(function(b) { if (b) b.classList.remove('is-active'); });
        hideFloatingPopover();

        if (mode === 'visual') {
            if (btnV) btnV.classList.add('is-active');
            if (toolbar) toolbar.style.display = 'flex';
            visualBox.style.display = 'block';
            rawTextarea.style.display = 'none';
            if (!isSplit) {
                sourcePane.style.display = 'block';
                previewPane.style.display = 'none';
            }
        } else if (mode === 'preview') {
            if (btnP) btnP.classList.add('is-active');
            if (toolbar) toolbar.style.display = 'none';
            syncContent(visualBox.style.display !== 'none' ? 'visual' : 'markdown');
            sourcePane.style.display = 'none';
            previewPane.style.display = 'block';
            renderLivePreview();
        } else if (mode === 'markdown') {
            if (btnM) btnM.classList.add('is-active');
            if (toolbar) toolbar.style.display = 'none';
            syncContent('visual');
            visualBox.style.display = 'none';
            rawTextarea.style.display = 'block';
            if (!isSplit) {
                sourcePane.style.display = 'block';
                previewPane.style.display = 'none';
            }
        }
    };

    window.toggleSplitView = function() {
        isSplit = !isSplit;
        var btn = document.getElementById('btnToggleSplit');
        hideFloatingPopover();
        if (isSplit) {
            btn.classList.add('is-active');
            container.classList.add('is-split-view');
            sourcePane.style.display = 'block';
            previewPane.style.display = 'block';
            syncContent(currentMode === 'markdown' ? 'markdown' : 'visual');
            renderLivePreview();
        } else {
            btn.classList.remove('is-active');
            container.classList.remove('is-split-view');
            switchEditorMode(currentMode);
        }
    };

    // ----------------------------------------------------
    // 6. Thao Tác Định Dạng Toolbar (ExecCommand)
    // ----------------------------------------------------
    window.execCmd = function(command, value) {
        visualBox.focus();
        document.execCommand(command, false, value || null);
        syncContent('visual');
    };

    window.executeHeading = function(val) {
        if (!val) return;
        visualBox.focus();
        document.execCommand('formatBlock', false, '<' + val + '>');
        syncContent('visual');
    };

    window.insertVisualCallout = function(type) {
        visualBox.focus();
        var title = type === 'note' ? '📘 GHI CHÚ' : (type === 'tip' ? '💡 MẸO HAY' : '⚠️ CẢNH BÁO');
        var html = '<div class="mayhen_visual_callout mayhen_visual_callout--' + type + '">' +
            '<div class="mayhen_visual_callout_header">' + title + '</div>' +
            '<p>Nhập nội dung thông tin lưu ý tại đây...</p>' +
            '</div><p><br></p>';
        document.execCommand('insertHTML', false, html);
        syncContent('visual');
    };

    window.insertVisualTable = function() {
        visualBox.focus();
        var html = '<table>' +
            '<tr><th>Bước / Hạng mục</th><th>Mô tả chi tiết</th><th>Lưu ý</th></tr>' +
            '<tr><td>Bước 1</td><td>Nhập thông tin cô dâu & chú rể</td><td>Ghi hoa trang trọng</td></tr>' +
            '<tr><td>Bước 2</td><td>Chọn ngày cưới và địa điểm tiệc</td><td>Tự động đổi Âm Lịch</td></tr>' +
            '</table><p><br></p>';
        document.execCommand('insertHTML', false, html);
        syncContent('visual');
    };

    window.insertVisualQuote = function() {
        visualBox.focus();
        var html = '<blockquote>"Tình yêu không phải là nhìn nhau, mà là cùng nhau nhìn về một hướng."</blockquote><p><br></p>';
        document.execCommand('insertHTML', false, html);
        syncContent('visual');
    };

    // ----------------------------------------------------
    // 7. Mẫu Khung Bài Viết Có Sẵn (Quick Templates)
    // ----------------------------------------------------
    window.applyQuickTemplate = function(tpl) {
        if (!tpl) return;
        if (visualBox.innerText.trim() !== '' && !confirm('Chọn mẫu mới sẽ thay thế toàn bộ nội dung hiện tại trong khung soạn thảo. Bạn có chắc chắn không?')) {
            return;
        }

        var docsBrand = @json($docsEditorBrandName);
        var tplHtml = '';
        if (tpl === 'guide') {
            tplHtml = '<h2>1. Giới thiệu tổng quan</h2>' +
                '<p>Chào mừng hai bạn đến với tính năng này của ' + docsBrand + '. Hướng dẫn này sẽ đồng hành cùng bạn thực hiện từng bước dễ dàng nhất.</p>' +
                '<div class="mayhen_visual_callout mayhen_visual_callout--note"><div class="mayhen_visual_callout_header">📘 GHI CHÚ</div><p>Bạn chỉ mất khoảng 2-3 phút để hoàn thành toàn bộ các bước bên dưới.</p></div>' +
                '<h2>2. Các bước triển khai thực hiện</h2>' +
                '<h3>Bước 1: Chọn mẫu thiệp yêu thích</h3>' +
                '<p>Truy cập vào kho mẫu thiệp cưới ' + docsBrand + ' và bấm vào nút <b>"Xem Thử Trực Tiếp"</b> để ngắm nhìn bản thiệp đầy đủ.</p>' +
                '<h3>Bước 2: Tùy chỉnh thông tin ngày cưới</h3>' +
                '<p>Nhập họ tên cô dâu, chú rể và thời gian cử hành hôn lễ. Hệ thống sẽ tự động quy đổi ngày Âm Lịch chuẩn xác.</p>' +
                '<h3>Bước 3: Tải ảnh cưới và phát nhạc</h3>' +
                '<p>Chọn những bức ảnh đẹp nhất của hai bạn để tạo album cưới 10 ảnh chuẩn thanh lịch.</p>' +
                '<div class="mayhen_visual_callout mayhen_visual_callout--tip"><div class="mayhen_visual_callout_header">💡 MẸO HAY</div><p>Nên chọn ảnh có độ phân giải từ 1200px trở lên để thiệp hiển thị sắc nét nhất trên mọi thiết bị.</p></div>' +
                '<h2>3. Tổng kết & Hoàn tất</h2>' +
                '<p>Bấm nút <b>"Xuất Bản Thiệp"</b> để nhận ngay liên kết gửi thiệp trang trọng đến những người thân yêu.</p>';
        } else if (tpl === 'feature') {
            tplHtml = '<h2>1. Giá trị mang lại của tính năng</h2>' +
                '<p>Tính năng này giúp cô dâu chú rể cá nhân hóa trải nghiệm mời cưới, mang lại sự sang trọng và tiện lợi vượt trội.</p>' +
                '<h2>2. Điểm nổi bật đáng chú ý</h2>' +
                '<ul>' +
                '<li><b>Phong cách Quiet Luxury:</b> Gam màu trang nhã, không gian thoáng đãng và font chữ thư pháp cổ điển.</li>' +
                '<li><b>Đồng bộ thời gian thực:</b> Mọi thao tác chỉnh sửa đều được cập nhật ngay lập tức.</li>' +
                '<li><b>Tương thích tuyệt đối:</b> Hiển thị mượt mà trên iPhone, Android và máy tính để bàn.</li>' +
                '</ul>' +
                '<div class="mayhen_visual_callout mayhen_visual_callout--tip"><div class="mayhen_visual_callout_header">💡 MẸO HAY</div><p>Bạn có thể thử nghiệm tính năng này trực tiếp ngay trên điện thoại của mình.</p></div>';
        } else if (tpl === 'sop') {
            tplHtml = '<h2>1. Mục đích & Phạm vi áp dụng</h2>' +
                '<p>Quy trình vận hành tiêu chuẩn này áp dụng cho toàn bộ nhân sự quản trị hệ thống ' + docsBrand + ' nhằm đảm bảo dữ liệu luôn chính xác và an toàn tuyệt đối.</p>' +
                '<div class="mayhen_visual_callout mayhen_visual_callout--warning"><div class="mayhen_visual_callout_header">⚠️ CẢNH BÁO</div><p>Mọi hành động can thiệp vào đơn hàng và giao dịch đều được tự động lưu lại trong sổ kiểm toán audit_logs.</p></div>' +
                '<h2>2. Các bước xử lý chuẩn hóa</h2>' +
                '<h3>Bước 1: Xác minh thông tin</h3>' +
                '<p>Kiểm tra kỹ mã đơn hàng và chứng từ giao dịch đối soát SePay VietQR.</p>' +
                '<h3>Bước 2: Phê duyệt và kích hoạt</h3>' +
                '<p>Bấm duyệt trạng thái đơn hàng và gửi thư điện tử chúc mừng tự động đến khách hàng.</p>';
        } else if (tpl === 'faq') {
            tplHtml = '<h2>1. Những câu hỏi thường gặp nhất</h2>' +
                '<h3>Tôi có thể chỉnh sửa thiệp sau khi đã gửi đi không?</h3>' +
                '<p>Hoàn toàn được! Bạn có thể vào lại Studio Tạo Thiệp để sửa bất kỳ lúc nào, khách mời mở link sẽ thấy bản cập nhật mới nhất ngay lập tức.</p>' +
                '<h3>Mẫu thiệp có hỗ trợ hiển thị ngày Âm Lịch không?</h3>' +
                '<p>Có, hệ thống tích hợp sẵn thuật toán thiên văn tự động tính ngày Âm Lịch Việt Nam chuẩn xác khi bạn chọn ngày Dương Lịch.</p>';
        }

        visualBox.innerHTML = tplHtml;
        attachVisualEditorInteractions();
        syncContent('visual');
    };

    // ----------------------------------------------------
    // 8. Quản Lý Modals: Hình Ảnh & Liên Kết
    // ----------------------------------------------------
    window.openInsertImageModal = function(isEdit, url, alt, caption) {
        saveSelection();
        var isEditing = !!isEdit;
        var heading = document.getElementById('modalImageHeadingTitle');
        var btn = document.getElementById('btnConfirmImageModal');
        var inputUrl = document.getElementById('inputImageUrl');
        var inputAlt = document.getElementById('inputImageAlt');
        var inputCap = document.getElementById('inputImageCaption');
        var selectImg = document.getElementById('selectDocsAvailableImage');

        if (heading) heading.innerText = isEditing ? '✏️ Chỉnh Sửa Hình Ảnh & Chú Thích' : '🖼️ Chèn Hình Ảnh Minh Họa';
        if (btn) btn.innerText = isEditing ? 'Cập Nhật Ảnh' : 'Chèn Ảnh Vào Bài';

        inputUrl.value = url || '';
        inputAlt.value = alt || '';
        inputCap.value = caption || '';
        if (selectImg) selectImg.value = url || '';

        updateImagePreviewBox(url || '');

        if (!isEditing) {
            activeFigureTarget = null;
        }

        document.getElementById('modalInsertImage').classList.add('is-open');
        inputUrl.focus();
    };

    window.onSelectDocsImageChanged = function(val) {
        if (!val) return;
        var inputUrl = document.getElementById('inputImageUrl');
        var inputAlt = document.getElementById('inputImageAlt');
        var inputCap = document.getElementById('inputImageCaption');

        inputUrl.value = val;
        updateImagePreviewBox(val);

        // Tự động tạo caption gợi ý từ tên tệp nếu chưa có
        if (!inputCap.value.trim()) {
            var fileName = val.split('/').pop().replace(/\.[^/.]+$/, "");
            var cleanName = fileName.replace(/[-_]/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });
            inputCap.value = cleanName;
        }
        if (!inputAlt.value.trim()) {
            inputAlt.value = inputCap.value;
        }
    };

    window.updateImagePreviewBox = function(url) {
        var box = document.getElementById('imagePreviewContainer');
        var thumb = document.getElementById('imagePreviewThumbnail');
        if (!url || url.trim() === '') {
            if (box) box.style.display = 'none';
            return;
        }
        if (thumb && box) {
            thumb.src = url;
            box.style.display = 'block';
        }
    };

    window.confirmInsertOrUpdateImage = function() {
        var url = document.getElementById('inputImageUrl').value.trim();
        var alt = document.getElementById('inputImageAlt').value.trim() || ('Ảnh minh họa ' + @json($docsEditorBrandName));
        var cap = document.getElementById('inputImageCaption').value.trim() || alt;

        if (!url) {
            alert('Vui lòng chọn hoặc nhập đường dẫn hình ảnh (URL).');
            return;
        }

        closeModal('modalInsertImage');

        if (activeFigureTarget) {
            // Chế độ Cập nhật ảnh hiện có
            var img = activeFigureTarget.querySelector('img');
            var capEl = activeFigureTarget.querySelector('figcaption');
            if (img) {
                img.setAttribute('src', url);
                img.setAttribute('alt', alt);
            }
            if (capEl) {
                capEl.innerText = cap;
            }
            activeFigureTarget.setAttribute('data-alt', alt);
            activeFigureTarget = null;
        } else {
            // Chế độ Chèn mới
            restoreSelection();
            visualBox.focus();
            var html = '<figure class="mayhen_visual_figure" data-alt="' + escapeAttr(alt) + '">' +
                '<div class="mayhen_fig_toolbar">' +
                    '<button type="button" class="mayhen_fig_btn" onclick="window.editActiveFigure(this)">✏️ Sửa ảnh</button>' +
                    '<button type="button" class="mayhen_fig_btn mayhen_fig_btn--delete" onclick="window.deleteActiveFigure(this)">🗑️ Xóa</button>' +
                '</div>' +
                '<img src="' + escapeAttr(url) + '" alt="' + escapeAttr(alt) + '" loading="lazy">' +
                '<figcaption contenteditable="true" data-placeholder="Nhập chú thích ảnh...">' + escapeHtml(cap) + '</figcaption>' +
                '</figure><p><br></p>';
            document.execCommand('insertHTML', false, html);
        }

        attachVisualEditorInteractions();
        syncContent('visual');
    };

    // Quản lý Modal Link
    window.openInsertLinkModal = function(isEdit, text, url) {
        saveSelection();
        var isEditing = !!isEdit;
        var heading = document.getElementById('modalLinkHeadingTitle');
        var btn = document.getElementById('btnConfirmLinkModal');
        var inputText = document.getElementById('inputLinkText');
        var inputUrl = document.getElementById('inputLinkUrl');
        var selectLink = document.getElementById('selectInternalDocLink');

        if (heading) heading.innerText = isEditing ? '✏️ Chỉnh Sửa Đường Dẫn Liên Kết' : '🔗 Chèn Đường Dẫn Liên Kết';
        if (btn) btn.innerText = isEditing ? 'Cập Nhật Link' : 'Chèn Link Vào Bài';

        var selectedText = window.getSelection().toString().trim();
        inputText.value = text || selectedText || '';
        inputUrl.value = url || '';

        if (selectLink) selectLink.value = url || '';

        if (!isEditing) {
            activeLinkTarget = null;
        }

        document.getElementById('modalInsertLink').classList.add('is-open');
        if (inputText.value) {
            inputUrl.focus();
        } else {
            inputText.focus();
        }
    };

    window.onSelectInternalDocChanged = function(sel) {
        var opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) return;

        var url = opt.value;
        var title = opt.getAttribute('data-title') || '';

        var inputUrl = document.getElementById('inputLinkUrl');
        var inputText = document.getElementById('inputLinkText');

        inputUrl.value = url;
        if (!inputText.value.trim() && title) {
            inputText.value = title;
        }
    };

    window.confirmInsertOrUpdateLink = function() {
        var text = document.getElementById('inputLinkText').value.trim() || ('Liên kết ' + @json($docsEditorBrandName));
        var url = document.getElementById('inputLinkUrl').value.trim();

        if (!url) {
            alert('Vui lòng nhập hoặc chọn địa chỉ web đích (URL).');
            return;
        }

        closeModal('modalInsertLink');

        if (activeLinkTarget) {
            // Chế độ cập nhật link hiện tại
            activeLinkTarget.setAttribute('href', url);
            activeLinkTarget.innerText = text;
            activeLinkTarget = null;
        } else {
            // Chế độ chèn link mới
            restoreSelection();
            visualBox.focus();
            var html = '<a href="' + escapeAttr(url) + '" target="_blank" rel="noopener">' + escapeHtml(text) + '</a>&nbsp;';
            document.execCommand('insertHTML', false, html);
        }

        hideFloatingPopover();
        syncContent('visual');
    };

    window.closeModal = function(id) {
        document.getElementById(id).classList.remove('is-open');
        activeFigureTarget = null;
        activeLinkTarget = null;
    };

    // ----------------------------------------------------
    // 9. Khởi Tạo Sự Kiện DOM & Form Submit
    // ----------------------------------------------------
    document.addEventListener('DOMContentLoaded', function() {
        var initialMd = rawTextarea.value.trim();
        if (initialMd) {
            visualBox.innerHTML = markdownToHtml(initialMd);
        } else {
            applyQuickTemplate('guide');
        }
        attachVisualEditorInteractions();

        // Lắng nghe gõ phím trên Visual Box
        visualBox.addEventListener('input', function() {
            syncContent('visual');
        });

        // Lắng nghe gõ phím trên Raw Textarea
        rawTextarea.addEventListener('input', function() {
            syncContent('markdown');
        });

        // Bắt sự kiện Click trên Visual Editor Box
        visualBox.addEventListener('click', function(e) {
            var anchor = e.target.closest('a');
            if (anchor) {
                e.preventDefault(); // Không navigate đi nơi khác trong trình soạn thảo
                showFloatingLinkPopover(anchor);
                return;
            } else {
                hideFloatingPopover();
            }

            var img = e.target.closest('img');
            if (img) {
                var fig = img.closest('.mayhen_visual_figure');
                if (fig) {
                    // Xóa selected ở các figure khác
                    visualBox.querySelectorAll('.mayhen_visual_figure').forEach(function(f) { f.classList.remove('is-selected'); });
                    fig.classList.add('is-selected');
                }
            } else {
                visualBox.querySelectorAll('.mayhen_visual_figure').forEach(function(f) { f.classList.remove('is-selected'); });
            }
        });

        // Double click vào ảnh để mở ngay modal sửa ảnh
        visualBox.addEventListener('dblclick', function(e) {
            var img = e.target.closest('img');
            if (img) {
                var fig = img.closest('.mayhen_visual_figure');
                if (fig) {
                    var btnEdit = fig.querySelector('.mayhen_fig_btn');
                    if (btnEdit) {
                        window.editActiveFigure(btnEdit);
                    }
                }
            }
        });

        // Đóng floating popover khi click ra ngoài editor
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#visualEditorBox') && !e.target.closest('#floatingLinkPopover')) {
                hideFloatingPopover();
            }
        });

        // Khi thay đổi tiêu đề hoặc tóm tắt: cập nhật Live Preview
        var titleInput = document.getElementById('doc_title');
        var summaryInput = document.getElementById('doc_summary');
        var catInput = document.getElementById('category_name');
        if (titleInput) titleInput.addEventListener('input', triggerLivePreview);
        if (summaryInput) summaryInput.addEventListener('input', triggerLivePreview);
        if (catInput) catInput.addEventListener('input', triggerLivePreview);

        // Trước khi submit form: luôn chuyển nội dung mới nhất sang textarea
        var form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                if (visualBox.style.display !== 'none') {
                    rawTextarea.value = htmlToMarkdown(visualBox.innerHTML);
                }
            });
        }

        // Render Live Preview lần đầu
        triggerLivePreview();
    });
})();
</script>
