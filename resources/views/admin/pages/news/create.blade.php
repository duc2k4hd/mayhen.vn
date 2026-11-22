@extends('admin.layouts.master')

@section('title', 'Đăng Bản Tin Mới — Tin Tức Mây Hẹn')

@section('content')
<div class="mayhen_admin_page" style="max-width: 1000px; margin: 0 auto;">
    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <div class="mayhen_admin_breadcrumb" style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 4px;">
                <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: none;">Tổng quan</a> /
                <a href="{{ route('admin.news.index') }}" style="color: inherit; text-decoration: none;">Tin tức dự án</a> /
                <span>Đăng bản tin mới</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin: 0; color: var(--mh-admin-heading);">
                Soạn Bản Tin &amp; Thông Báo Tính Năng Mới
            </h1>
        </div>
        <a href="{{ route('admin.news.index') }}" style="padding: 9px 16px; border-radius: 8px; border: 1px solid var(--mh-admin-border); background: #fff; text-decoration: none; color: var(--mh-admin-text); font-size: 0.875rem;">
            &larr; Quay lại danh sách
        </a>
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

    <form method="POST" action="{{ route('admin.news.store') }}" enctype="multipart/form-data">
        @csrf

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
            {{-- Cột Trái: Nội Dung Chính --}}
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div style="background: #fff; padding: 24px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle);">
                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 6px;">Tiêu Đề Bản Tin <span style="color: red;">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" required placeholder="Ví dụ: Ra mắt bộ sưu tập mẫu thiệp cưới Hoàng Gia Pro & tính năng lọc thiệp thông minh..." style="width: 100%; padding: 12px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 1rem; font-weight: 600;">
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 6px;">Đường dẫn tĩnh (/tin-tuc/{slug} - Để trống tự sinh theo tiêu đề)</label>
                        <input type="text" name="slug" value="{{ old('slug') }}" placeholder="ra-mat-bo-suu-tap-mau-thiep-hoang-gia-pro" style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem; color: #666;">
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 6px;">Tóm Tắt Bản Tin (Nội dung này sẽ được gửi kèm trong Email tóm tắt tới toàn bộ User)</label>
                        <textarea name="summary" rows="4" placeholder="Tóm tắt ngắn gọn 2-4 câu những điểm nổi bật nhất của bản cập nhật này để gửi vào hộp thư thành viên..." style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">{{ old('summary') }}</textarea>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 6px;">Nội Dung Chi Tiết Bản Tin (Hỗ trợ HTML, thẻ &lt;h2&gt;, &lt;h3&gt; tự sinh mục lục) <span style="color: red;">*</span></label>
                        <textarea name="content" rows="18" required placeholder="<h2>1. Điểm mới nổi bật</h2><p>Mô tả chi tiết tính năng mới trên Mây Hẹn...</p>" style="width: 100%; padding: 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.9375rem; line-height: 1.6;">{{ old('content') }}</textarea>
                    </div>
                </div>

                {{-- Khối Cấu Hình SEO --}}
                <div style="background: #fff; padding: 24px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle);">
                    <h3 style="font-size: 1rem; font-weight: 700; margin: 0 0 16px 0; color: var(--mh-admin-heading);">Tối Ưu SEO &amp; Chia Sẻ Mạng Xã Hội</h3>
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 4px;">Thẻ Tiêu Đề SEO (Meta Title)</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title') }}" placeholder="Tiêu đề hiển thị trên Google..." style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">
                    </div>
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 4px;">Mô Tả SEO (Meta Description)</label>
                        <textarea name="meta_description" rows="2" placeholder="Mô tả khoảng 150-160 ký tự..." style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">{{ old('meta_description') }}</textarea>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 4px;">Từ Khóa (Meta Keywords)</label>
                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords') }}" placeholder="tin tức mây hẹn, tính năng mới, mẫu thiệp cưới mới" style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">
                    </div>
                </div>
            </div>

            {{-- Cột Phải: Xuất Bản & Hàng Đợi Email --}}
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle);">
                    <h3 style="font-size: 0.9375rem; font-weight: 700; margin: 0 0 16px 0;">Xuất Bản &amp; Thông Báo Email</h3>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 6px;">Trạng Thái</label>
                        <select name="status" style="width: 100%; padding: 10px 12px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem; background: #fff;">
                            <option value="published" {{ old('status', 'published') === 'published' ? 'selected' : '' }}>Xuất bản ngay (Live)</option>
                            <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Lưu bản nháp</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 6px;">Ngày Xuất Bản</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at') }}" style="width: 100%; padding: 10px 12px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.875rem; cursor: pointer;">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                            <span>Đặt làm Bản Tin Nổi Bật</span>
                        </label>
                    </div>

                    <div style="margin-bottom: 18px; padding: 12px 14px; border-radius: 8px; background: #F5F9FF; border: 1px solid #BBDEFB;">
                        <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 0.84rem; cursor: pointer; color: #0D47A1; font-weight: 600;">
                            <input type="checkbox" name="send_email_on_publish" value="1" {{ old('send_email_on_publish', '1') ? 'checked' : '' }} style="margin-top: 3px;">
                            <span>Gửi email tóm tắt cho toàn bộ User đã đăng ký (Đưa vào hàng đợi Queue)</span>
                        </label>
                        <p style="font-size: 0.75rem; color: #546E7A; margin: 6px 0 0 22px; line-height: 1.5;">
                            Khi xuất bản, hệ thống sẽ tự động đẩy thư tóm tắt kèm link <code>/tin-tuc/slug</code> vào hàng đợi để gửi tới mọi thành viên Mây Hẹn.
                        </p>
                    </div>

                    <button type="submit" style="width: 100%; padding: 12px; border-radius: 8px; background: #C5A880; color: #fff; border: none; font-size: 0.9375rem; font-weight: 600; cursor: pointer;">
                        Đăng Bản Tin &amp; Xử Lý Hàng Đợi
                    </button>
                </div>

                <div style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle);">
                    <h3 style="font-size: 0.9375rem; font-weight: 700; margin: 0 0 16px 0;">Phân Loại &amp; Tác Giả</h3>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 6px;">Nhóm Bản Tin</label>
                        <select name="category_label" style="width: 100%; padding: 10px 12px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem; background: #fff;">
                            @foreach($categoryLabels as $label)
                                <option value="{{ $label }}" {{ old('category_label') === $label ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 6px;">Tên Tác Giả / Ban Phát Hành</label>
                        <input type="text" name="author_name" value="{{ old('author_name', 'Ban Phát Triển Mây Hẹn') }}" style="width: 100%; padding: 10px 12px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 6px;">Tags (Cách nhau bằng dấu phẩy)</label>
                        <input type="text" name="tags" value="{{ old('tags') }}" placeholder="cập nhật mới, mẫu thiệp pro, mây hẹn" style="width: 100%; padding: 10px 12px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">
                    </div>
                </div>

                <div style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle);">
                    <h3 style="font-size: 0.9375rem; font-weight: 700; margin: 0 0 16px 0;">Ảnh Bìa Bản Tin</h3>
                    <div>
                        <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" style="width: 100%; font-size: 0.8125rem;">
                        <p style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 8px;">Khuyên dùng ảnh tỉ lệ 16:9 (1200x675px), định dạng WebP/JPG dưới 4MB.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
