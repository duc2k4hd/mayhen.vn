@extends('admin.layouts.master')

@section('title', 'Chỉnh Sửa Bài Viết: ' . $post->title)

@section('content')
<div class="mayhen_admin_page" style="max-width: 1000px; margin: 0 auto;">
    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <div class="mayhen_admin_breadcrumb" style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 4px;">
                <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: none;">Tổng quan</a> / 
                <a href="{{ route('admin.blog.index') }}" style="color: inherit; text-decoration: none;">Cẩm nang cưới</a> / 
                <span>Chỉnh sửa bài viết</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin: 0; color: var(--mh-admin-heading);">
                Chỉnh Sửa Bài Viết #{{ $post->id }}
            </h1>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" style="padding: 9px 14px; border-radius: 8px; border: 1px solid #C5A880; background: #FFFDF9; color: #9C7A4A; text-decoration: none; font-size: 0.875rem; font-weight: 600;">
                Xem Bài Viết &nearr;
            </a>
            <a href="{{ route('admin.blog.index') }}" style="padding: 9px 16px; border-radius: 8px; border: 1px solid var(--mh-admin-border); background: #fff; text-decoration: none; color: var(--mh-admin-text); font-size: 0.875rem;">
                &larr; Quay lại
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div style="padding: 14px 18px; border-radius: 8px; background: #FFEBEE; border: 1px solid #FFCDD2; color: #C62828; margin-bottom: 20px; font-size: 0.875rem;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.blog.update', $post->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
            {{-- Cột Trái --}}
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div style="background: #fff; padding: 24px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle);">
                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 6px;">Tiêu Đề Bài Viết <span style="color: red;">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $post->title) }}" required style="width: 100%; padding: 12px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 1rem; font-weight: 600;">
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 6px;">Đường dẫn tĩnh (Slug URL)</label>
                        <input type="text" name="slug" value="{{ old('slug', $post->slug) }}" style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem; color: #666;">
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 6px;">Tóm Tắt Ngắn (Lead Summary)</label>
                        <textarea name="summary" rows="3" style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">{{ old('summary', $post->summary) }}</textarea>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 6px;">Nội Dung Bài Viết (Hỗ trợ HTML, thẻ &lt;h2&gt;, &lt;h3&gt; để sinh mục lục) <span style="color: red;">*</span></label>
                        <textarea name="content" rows="18" required style="width: 100%; padding: 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.9375rem; line-height: 1.6;">{{ old('content', $post->content) }}</textarea>
                    </div>
                </div>

                {{-- Khối Cấu Hình SEO --}}
                <div style="background: #fff; padding: 24px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle);">
                    <h3 style="font-size: 1rem; font-weight: 700; margin: 0 0 16px 0; color: var(--mh-admin-heading);">Tối Ưu SEO &amp; Chia Sẻ Mạng Xã Hội</h3>
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 4px;">Thẻ Tiêu Đề SEO (Meta Title)</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">
                    </div>
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 4px;">Mô Tả SEO (Meta Description)</label>
                        <textarea name="meta_description" rows="2" style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">{{ old('meta_description', $post->meta_description) }}</textarea>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 4px;">Từ Khóa (Meta Keywords)</label>
                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $post->meta_keywords) }}" style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">
                    </div>
                </div>
            </div>

            {{-- Cột Phải --}}
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle);">
                    <h3 style="font-size: 0.9375rem; font-weight: 700; margin: 0 0 16px 0;">Thiết Lập Xuất Bản</h3>
                    
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 6px;">Trạng Thái</label>
                        <select name="status" style="width: 100%; padding: 10px 12px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem; background: #fff;">
                            <option value="published" {{ old('status', $post->status) === 'published' ? 'selected' : '' }}>Xuất bản (Live)</option>
                            <option value="draft" {{ old('status', $post->status) === 'draft' ? 'selected' : '' }}>Lưu bản nháp</option>
                            <option value="scheduled" {{ old('status', $post->status) === 'scheduled' ? 'selected' : '' }}>Hẹn giờ đăng</option>
                            <option value="archived" {{ old('status', $post->status) === 'archived' ? 'selected' : '' }}>Lưu trữ ẩn</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 6px;">Ngày Xuất Bản</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}" style="width: 100%; padding: 10px 12px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.875rem; cursor: pointer;">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $post->is_featured) ? 'checked' : '' }}>
                            <span>Đặt làm Bài Viết Nổi Bật (Featured)</span>
                        </label>
                    </div>

                    <button type="submit" style="width: 100%; padding: 12px; border-radius: 8px; background: #C5A880; color: #fff; border: none; font-size: 0.9375rem; font-weight: 600; cursor: pointer;">
                        Cập Nhật Thay Đổi
                    </button>
                </div>

                <div style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle);">
                    <h3 style="font-size: 0.9375rem; font-weight: 700; margin: 0 0 16px 0;">Phân Loại &amp; Tác Giả</h3>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 6px;">Chuyên Mục</label>
                        <select name="category_id" style="width: 100%; padding: 10px 12px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem; background: #fff;">
                            <option value="">-- Chọn Chuyên Mục --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $post->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 6px;">Tên Tác Giả / Bút Danh</label>
                        <input type="text" name="author_name" value="{{ old('author_name', $post->author_name) }}" style="width: 100%; padding: 10px 12px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 6px;">Tags</label>
                        <input type="text" name="tags" value="{{ old('tags', is_array($post->tags) ? implode(', ', $post->tags) : '') }}" style="width: 100%; padding: 10px 12px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">
                    </div>
                </div>

                <div style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle);">
                    <h3 style="font-size: 0.9375rem; font-weight: 700; margin: 0 0 16px 0;">Ảnh Bìa Hiện Tại</h3>
                    @if($post->cover_image_url)
                        <div style="margin-bottom: 12px;">
                            <img src="{{ $post->cover_image_url }}" alt="" style="width: 100%; height: 140px; object-fit: cover; border-radius: 8px; border: 1px solid #eee;">
                        </div>
                    @endif
                    <div>
                        <label style="display: block; font-size: 0.8125rem; margin-bottom: 4px;">Thay đổi ảnh bìa:</label>
                        <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" style="width: 100%; font-size: 0.8125rem;">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

