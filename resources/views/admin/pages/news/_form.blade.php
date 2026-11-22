@php
    $isEdit = isset($post) && $post !== null;
    $formAction = $isEdit ? route('admin.news.update', $post->id) : route('admin.news.store');
    $currentCover = old('cover_image_url', $isEdit ? $post->cover_image_url : '');
    $currentTags = old('tags', ($isEdit && is_array($post->tags)) ? implode(', ', $post->tags) : '');
    $currentPublishedAt = old('published_at', ($isEdit && $post->published_at) ? $post->published_at->format('Y-m-d\TH:i') : '');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/admin/css/pages/news.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset_v('assets/admin/vendor/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset_v('assets/admin/js/pages/news-editor.js') }}"></script>
@endpush

<div class="mayhen_admin_news_wrap">
    {{-- Topbar --}}
    <div class="mayhen_admin_news_topbar">
        <div>
            <div class="mayhen_admin_news_breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Tổng quan</a> /
                <a href="{{ route('admin.news.index') }}">Tin tức dự án</a> /
                <span>{{ $isEdit ? 'Chỉnh sửa bản tin #' . $post->id : 'Đăng bản tin mới' }}</span>
            </div>
            <h1 class="mayhen_admin_news_heading">
                {{ $isEdit ? 'Chỉnh Sửa Bản Tin #' . $post->id : 'Soạn Bản Tin & Thông Báo Mới' }}
            </h1>
        </div>

        <div class="mayhen_admin_news_topbar_actions">
            @if($isEdit)
                <a href="{{ route('news.show', $post->slug) }}" target="_blank" rel="noopener" class="mayhen_admin_news_btn_gold">
                    Xem Bản Tin &nearr;
                </a>
            @endif
            <a href="{{ route('admin.news.index') }}" class="mayhen_admin_news_btn_ghost">
                &larr; Quay lại danh sách
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mayhen_admin_news_errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        id="mayhenAdminNewsForm"
        method="POST"
        action="{{ $formAction }}"
        enctype="multipart/form-data"
        data-upload-url="{{ route('admin.news.upload_image') }}"
        data-media-url="{{ route('admin.news.media_library') }}"
        data-public-base-url="{{ url('/tin-tuc') }}"
    >
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="mayhen_admin_news_grid">
            {{-- Cột Trái: Tiêu đề, TinyMCE & SEO --}}
            <div class="mayhen_admin_news_col_main">
                <section class="mayhen_admin_news_card">
                    <div class="mayhen_admin_news_card_head">
                        <h2 class="mayhen_admin_news_card_title">Nội Dung Bản Tin</h2>
                        <span class="mayhen_admin_news_stats_pill" id="mayhenEditorStats">0 từ &bull; ~1 phút đọc</span>
                    </div>

                    <div class="mayhen_admin_news_field">
                        <label for="mayhenNewsTitle" class="mayhen_admin_news_label">
                            Tiêu Đề Bản Tin <span class="mayhen_admin_news_req">*</span>
                        </label>
                        <input
                            id="mayhenNewsTitle"
                            type="text"
                            name="title"
                            value="{{ old('title', $isEdit ? $post->title : '') }}"
                            required
                            placeholder="Ví dụ: Ra mắt mẫu thiệp cưới Hoàng Gia Pro & nâng cấp Studio V2..."
                            class="mayhen_admin_news_input mayhen_admin_news_input_title"
                        >
                    </div>

                    <div class="mayhen_admin_news_field">
                        <label for="mayhenNewsSlug" class="mayhen_admin_news_label">Đường dẫn tĩnh (Slug chuẩn SEO)</label>
                        <div class="mayhen_admin_news_slug_group">
                            <span class="mayhen_admin_news_slug_prefix">/tin-tuc/</span>
                            <input
                                id="mayhenNewsSlug"
                                type="text"
                                name="slug"
                                value="{{ old('slug', $isEdit ? $post->slug : '') }}"
                                placeholder="tu-dong-tao-tu-tieu-de"
                                class="mayhen_admin_news_input mayhen_admin_news_slug_input"
                            >
                            <button type="button" id="mayhenRegenSlugBtn" class="mayhen_admin_news_slug_regen">
                                Tạo lại slug
                            </button>
                        </div>
                    </div>

                    <div class="mayhen_admin_news_field">
                        <label for="mayhenNewsSummary" class="mayhen_admin_news_label">
                            Tóm Tắt Bản Tin (Hiển thị đầu bài &amp; gửi kèm trong Email tóm tắt tới User)
                        </label>
                        <textarea
                            id="mayhenNewsSummary"
                            name="summary"
                            rows="3"
                            placeholder="Tóm tắt ngắn gọn 2–3 câu những điểm nổi bật nhất của bản tin..."
                            class="mayhen_admin_news_textarea"
                        >{{ old('summary', $isEdit ? $post->summary : '') }}</textarea>
                    </div>

                    <div class="mayhen_admin_news_field">
                        <div class="mayhen_admin_news_editor_toolbar">
                            <label for="mayhenAdminNewsContent" class="mayhen_admin_news_label">
                                Nội Dung Chi Tiết (TinyMCE — Kéo thả ảnh trực tiếp hoặc chọn từ Thư viện) <span class="mayhen_admin_news_req">*</span>
                            </label>
                            <button type="button" id="mayhenOpenEditorLibraryBtn" class="mayhen_admin_news_btn_gold">
                                🖼 Chèn Ảnh Từ Thư Viện
                            </button>
                        </div>
                        <textarea
                            id="mayhenAdminNewsContent"
                            name="content"
                            rows="18"
                            required
                            class="mayhen_admin_news_textarea"
                        >{{ old('content', $isEdit ? $post->content : '') }}</textarea>
                    </div>
                </section>

                {{-- Khối SEO & Xem Trước Google SERP --}}
                <section class="mayhen_admin_news_card">
                    <div class="mayhen_admin_news_card_head">
                        <h2 class="mayhen_admin_news_card_title">Tối Ưu SEO &amp; Xem Trước Kết Quả Google</h2>
                    </div>

                    <div class="mayhen_admin_news_serp_box">
                        <div class="mayhen_admin_news_serp_label">Xem trước hiển thị trên Google</div>
                        <div class="mayhen_admin_news_serp_url" id="mayhenSerpUrl">{{ url('/tin-tuc/' . ($isEdit ? $post->slug : 'ban-tin-moi')) }}</div>
                        <div class="mayhen_admin_news_serp_title" id="mayhenSerpTitle">{{ old('meta_title', $isEdit ? ($post->meta_title ?: $post->title) : 'Tiêu đề bản tin Mây Hẹn') }}</div>
                        <div class="mayhen_admin_news_serp_desc" id="mayhenSerpDesc">{{ old('meta_description', $isEdit ? ($post->meta_description ?: $post->summary) : 'Mô tả ngắn chuẩn SEO 140–160 ký tự hiển thị trên Google...') }}</div>
                    </div>

                    <div class="mayhen_admin_news_field">
                        <div class="mayhen_admin_news_label_row">
                            <label for="mayhenNewsMetaTitle" class="mayhen_admin_news_label">Thẻ Tiêu Đề SEO (Meta Title)</label>
                            <span class="mayhen_admin_news_counter" id="mayhenMetaTitleCounter">0/60 ký tự</span>
                        </div>
                        <input
                            id="mayhenNewsMetaTitle"
                            type="text"
                            name="meta_title"
                            value="{{ old('meta_title', $isEdit ? $post->meta_title : '') }}"
                            placeholder="Nên dài 50–60 ký tự (để trống tự lấy theo tiêu đề)"
                            class="mayhen_admin_news_input"
                        >
                    </div>

                    <div class="mayhen_admin_news_field">
                        <div class="mayhen_admin_news_label_row">
                            <label for="mayhenNewsMetaDesc" class="mayhen_admin_news_label">Mô Tả SEO (Meta Description)</label>
                            <span class="mayhen_admin_news_counter" id="mayhenMetaDescCounter">0/160 ký tự</span>
                        </div>
                        <textarea
                            id="mayhenNewsMetaDesc"
                            name="meta_description"
                            rows="2"
                            placeholder="Nên dài 140–160 ký tự mô tả đúng giá trị bản tin..."
                            class="mayhen_admin_news_textarea"
                        >{{ old('meta_description', $isEdit ? $post->meta_description : '') }}</textarea>
                    </div>

                    <div class="mayhen_admin_news_field">
                        <label for="mayhenNewsMetaKeywords" class="mayhen_admin_news_label">Từ Khóa (Meta Keywords)</label>
                        <input
                            id="mayhenNewsMetaKeywords"
                            type="text"
                            name="meta_keywords"
                            value="{{ old('meta_keywords', $isEdit ? $post->meta_keywords : '') }}"
                            placeholder="tin tức mây hẹn, mẫu thiệp cưới mới, thiệp cưới online"
                            class="mayhen_admin_news_input"
                        >
                    </div>
                </section>
            </div>

            {{-- Cột Phải: Xuất Bản, Phân Loại & Ảnh Bìa --}}
            <aside class="mayhen_admin_news_col_side">
                <section class="mayhen_admin_news_card">
                    <div class="mayhen_admin_news_card_head">
                        <h2 class="mayhen_admin_news_card_title">Xuất Bản &amp; Hàng Đợi Email</h2>
                    </div>

                    <div class="mayhen_admin_news_field">
                        <label for="mayhenNewsStatus" class="mayhen_admin_news_label">Trạng Thái</label>
                        <select id="mayhenNewsStatus" name="status" class="mayhen_admin_news_select">
                            <option value="published" {{ old('status', $isEdit ? $post->status : 'published') === 'published' ? 'selected' : '' }}>Xuất bản ngay (Live)</option>
                            <option value="draft" {{ old('status', $isEdit ? $post->status : '') === 'draft' ? 'selected' : '' }}>Lưu bản nháp</option>
                            <option value="scheduled" {{ old('status', $isEdit ? $post->status : '') === 'scheduled' ? 'selected' : '' }}>Hẹn giờ xuất bản</option>
                            @if($isEdit)
                                <option value="archived" {{ old('status', $post->status) === 'archived' ? 'selected' : '' }}>Lưu trữ ẩn</option>
                            @endif
                        </select>
                    </div>

                    <div class="mayhen_admin_news_field">
                        <label for="mayhenNewsPublishedAt" class="mayhen_admin_news_label">Thời Gian Xuất Bản</label>
                        <input
                            id="mayhenNewsPublishedAt"
                            type="datetime-local"
                            name="published_at"
                            value="{{ $currentPublishedAt }}"
                            class="mayhen_admin_news_input"
                        >
                    </div>

                    <div class="mayhen_admin_news_field">
                        <label class="mayhen_admin_news_checkbox">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $isEdit ? $post->is_featured : false) ? 'checked' : '' }}>
                            <span>Đặt làm <strong>Bản Tin Nổi Bật</strong> đầu trang</span>
                        </label>
                    </div>

                    <div class="mayhen_admin_news_email_box">
                        <label class="mayhen_admin_news_checkbox">
                            <input
                                type="checkbox"
                                name="send_email_on_publish"
                                value="1"
                                {{ old('send_email_on_publish', $isEdit ? $post->send_email_on_publish : true) ? 'checked' : '' }}
                            >
                            <span><strong>Gửi email tóm tắt</strong> cho toàn bộ User đã đăng ký (Queue)</span>
                        </label>
                        @if($isEdit && $post->email_broadcasted_at)
                            <p class="mayhen_admin_news_email_done">
                                ✓ Đã đưa vào hàng đợi gửi {{ number_format($post->email_recipients_count) }} user lúc {{ $post->email_broadcasted_at->format('d/m/Y H:i') }}.
                            </p>
                        @else
                            <p class="mayhen_admin_news_email_note">
                                Khi xuất bản, hệ thống tự đẩy email tóm tắt kèm đường dẫn bản tin vào hàng đợi gửi thành viên.
                            </p>
                        @endif
                    </div>

                    <button type="submit" class="mayhen_admin_news_submit_btn">
                        {{ $isEdit ? '✦ Lưu Thay Đổi Bản Tin' : '✦ Đăng Bản Tin & Xử Lý Hàng Đợi' }}
                    </button>
                </section>

                <section class="mayhen_admin_news_card">
                    <div class="mayhen_admin_news_card_head">
                        <h2 class="mayhen_admin_news_card_title">Ảnh Bìa Bản Tin (Cover)</h2>
                    </div>

                    <input type="hidden" name="cover_image_url" id="mayhenCoverUrlHidden" value="{{ $currentCover }}">
                    <input type="hidden" name="remove_cover_image" id="mayhenRemoveCoverHidden" value="0">
                    <input type="file" name="cover_image" id="mayhenCoverFileInput" accept="image/jpeg,image/png,image/webp" class="mayhen_admin_news_hidden">

                    <div id="mayhenCoverPreviewWrap" class="mayhen_admin_news_cover_preview {{ empty($currentCover) ? 'mayhen_admin_news_hidden' : '' }}">
                        <img id="mayhenCoverPreviewImg" src="{{ $currentCover }}" alt="Xem trước ảnh bìa">
                        <button type="button" id="mayhenCoverRemoveBtn" class="mayhen_admin_news_cover_remove">✕ Xóa ảnh</button>
                    </div>

                    <div id="mayhenCoverDropzone" class="mayhen_admin_news_dropzone">
                        <p class="mayhen_admin_news_dropzone_title">Kéo thả ảnh bìa hoặc bấm để tải lên</p>
                        <p class="mayhen_admin_news_dropzone_sub">Tự tối ưu WebP &bull; Tỉ lệ 16:9 (JPG, PNG, WebP &le; 5MB)</p>
                    </div>

                    <div class="mayhen_admin_news_cover_actions">
                        <button type="button" id="mayhenOpenCoverLibraryBtn" class="mayhen_admin_news_btn_gold">
                            🖼 Chọn Ảnh Bìa Từ Thư Viện
                        </button>
                    </div>
                </section>

                <section class="mayhen_admin_news_card">
                    <div class="mayhen_admin_news_card_head">
                        <h2 class="mayhen_admin_news_card_title">Phân Loại &amp; Tác Giả</h2>
                    </div>

                    <div class="mayhen_admin_news_field">
                        <label for="mayhenNewsCategory" class="mayhen_admin_news_label">Nhóm Bản Tin</label>
                        <select id="mayhenNewsCategory" name="category_label" class="mayhen_admin_news_select">
                            @foreach($categoryLabels as $label)
                                <option value="{{ $label }}" {{ old('category_label', $isEdit ? $post->category_label : '') === $label ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mayhen_admin_news_field">
                        <label for="mayhenNewsAuthor" class="mayhen_admin_news_label">Tác Giả / Ban Phát Hành</label>
                        <input
                            id="mayhenNewsAuthor"
                            type="text"
                            name="author_name"
                            value="{{ old('author_name', $isEdit ? $post->author_name : 'Ban Phát Triển Mây Hẹn') }}"
                            class="mayhen_admin_news_input"
                        >
                    </div>

                    <div class="mayhen_admin_news_field">
                        <label for="mayhenNewsTags" class="mayhen_admin_news_label">Thẻ từ khóa (Tags, cách nhau dấu phẩy)</label>
                        <input
                            id="mayhenNewsTags"
                            type="text"
                            name="tags"
                            value="{{ $currentTags }}"
                            placeholder="mẫu thiệp mới, cập nhật hệ thống, mây hẹn"
                            class="mayhen_admin_news_input"
                        >
                    </div>
                </section>
            </aside>
        </div>
    </form>
</div>

{{-- Modal Thư Viện Ảnh Nội Bộ --}}
<div id="mayhenMediaModal" class="mayhen_admin_news_modal" role="dialog" aria-modal="true" aria-labelledby="mayhenMediaModalTitle">
    <div class="mayhen_admin_news_modal_dialog">
        <div class="mayhen_admin_news_modal_head">
            <h3 id="mayhenMediaModalTitle" class="mayhen_admin_news_modal_title">Thư Viện Ảnh Tin Tức Mây Hẹn</h3>
            <div class="mayhen_admin_news_modal_tools">
                <input type="search" id="mayhenMediaSearch" placeholder="Tìm nhanh tên ảnh..." class="mayhen_admin_news_input" style="width: 240px; padding: 7px 12px;">
                <button type="button" id="mayhenMediaCloseBtn" class="mayhen_admin_news_btn_ghost">Đóng</button>
            </div>
        </div>
        <div class="mayhen_admin_news_modal_body">
            <div id="mayhenMediaGrid" class="mayhen_admin_news_media_grid"></div>
        </div>
    </div>
</div>
