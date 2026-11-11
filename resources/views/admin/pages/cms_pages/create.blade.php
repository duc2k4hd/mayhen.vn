@extends('admin.layouts.master')

@section('title', 'Tạo Trang Nội Dung Mới')

@section('content')
<div class="mayhen_admin_page" style="max-width: 900px; margin: 0 auto;">
    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <div class="mayhen_admin_breadcrumb" style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 4px;">
                <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: none;">Tổng quan</a> / 
                <a href="{{ route('admin.pages.index') }}" style="color: inherit; text-decoration: none;">Trang nội dung tĩnh</a> / 
                <span>Tạo trang mới</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin: 0; color: var(--mh-admin-heading);">
                Tạo Trang Nội Dung Mới
            </h1>
        </div>
        <a href="{{ route('admin.pages.index') }}" style="padding: 9px 16px; border-radius: 8px; border: 1px solid var(--mh-admin-border); background: #fff; text-decoration: none; color: var(--mh-admin-text); font-size: 0.875rem;">
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

    <form method="POST" action="{{ route('admin.pages.store') }}">
        @csrf

        <div style="background: #fff; padding: 24px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); margin-bottom: 24px;">
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 6px;">Tiêu Đề Trang <span style="color: red;">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="Ví dụ: Quy Chế Giải Quyết Khiếu Nại..." style="width: 100%; padding: 12px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 1rem; font-weight: 600;">
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 6px;">Đường Dẫn Tĩnh (Slug URL)</label>
                <input type="text" name="slug" value="{{ old('slug') }}" placeholder="Để trống hệ thống sẽ tự động tạo từ tiêu đề (ví dụ: quy-che-giai-quyet-khieu-nai)" style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 6px;">Lời Mở Đầu / Tóm Tắt (Lead Summary)</label>
                <textarea name="lead_summary" rows="3" placeholder="Đoạn văn ngắn giới thiệu nội dung trang..." style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">{{ old('lead_summary') }}</textarea>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 6px;">Nội Dung Trang (Hỗ trợ định dạng HTML, thẻ &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;blockquote&gt;) <span style="color: red;">*</span></label>
                <textarea name="content" rows="18" required placeholder="Nội dung chi tiết trang..." style="width: 100%; padding: 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.9375rem; line-height: 1.6;">{{ old('content') }}</textarea>
            </div>

            <div style="margin-bottom: 10px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 0.875rem; cursor: pointer;">
                    <input type="checkbox" name="is_published" value="1" {{ old('is_published', true) ? 'checked' : '' }}>
                    <span style="font-weight: 600;">Xuất bản công khai trên website ngay</span>
                </label>
            </div>
        </div>

        {{-- Cấu hình SEO --}}
        <div style="background: #fff; padding: 24px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); margin-bottom: 24px;">
            <h3 style="font-size: 1rem; font-weight: 700; margin: 0 0 16px 0; color: var(--mh-admin-heading);">Cấu Hình SEO Cho Trang</h3>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 4px;">Thẻ Tiêu Đề SEO (Meta Title)</label>
                <input type="text" name="meta_title" value="{{ old('meta_title') }}" placeholder="Mặc định lấy theo tiêu đề trang" style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 4px;">Thẻ Mô Tả SEO (Meta Description)</label>
                <textarea name="meta_description" rows="2" placeholder="Tối đa 160 ký tự..." style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">{{ old('meta_description') }}</textarea>
            </div>
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 4px;">Từ Khóa SEO (Meta Keywords)</label>
                <input type="text" name="meta_keywords" value="{{ old('meta_keywords') }}" style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">
            </div>
        </div>

        <button type="submit" style="padding: 12px 28px; border-radius: 8px; background: #C5A880; color: #fff; border: none; font-size: 0.9375rem; font-weight: 600; cursor: pointer;">
            Tạo Trang Nội Dung
        </button>
    </form>
</div>
@endsection

