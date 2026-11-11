@extends('admin.layouts.master')

@section('title', 'Chuyên Mục Cẩm Nang Cưới')

@section('content')
<div class="mayhen_admin_page" style="max-width: 100%; margin: 0 auto;">
    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <div class="mayhen_admin_breadcrumb" style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 4px;">
                <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: none;">Tổng quan</a> / 
                <a href="{{ route('admin.blog.index') }}" style="color: inherit; text-decoration: none;">Cẩm nang cưới</a> / 
                <span>Chuyên mục</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin: 0; color: var(--mh-admin-heading);">
                Quản Lý Chuyên Mục Cẩm Nang
            </h1>
        </div>
        <a href="{{ route('admin.blog.index') }}" style="padding: 9px 16px; border-radius: 8px; border: 1px solid var(--mh-admin-border); background: #fff; text-decoration: none; color: var(--mh-admin-text); font-size: 0.875rem;">
            &larr; Về danh sách bài viết
        </a>
    </div>

    @if(session('success'))
        <div style="padding: 14px 18px; border-radius: 8px; background: #E8F5E9; border: 1px solid #C8E6C9; color: #2E7D32; margin-bottom: 20px; font-size: 0.875rem;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        {{-- Danh sách chuyên mục hiện có --}}
        <div style="background: #fff; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); overflow: hidden;">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--mh-admin-border); background: #FAF8F5;">
                <h3 style="margin: 0; font-size: 0.9375rem; font-weight: 700; color: var(--mh-admin-heading);">Danh Mục Đang Hoạt Động</h3>
            </div>
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--mh-admin-border-subtle); color: var(--mh-admin-muted); font-size: 0.75rem; text-transform: uppercase;">
                        <th style="padding: 12px 18px;">Tên Chuyên Mục</th>
                        <th style="padding: 12px 18px;">Slug</th>
                        <th style="padding: 12px 18px; text-align: center;">Số Bài</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr style="border-bottom: 1px solid var(--mh-admin-border-subtle);">
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 600; color: var(--mh-admin-heading);">{{ $category->name }}</div>
                                @if($category->description)
                                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 2px;">{{ Str::limit($category->description, 50) }}</div>
                                @endif
                            </td>
                            <td style="padding: 14px 18px; color: #666; font-family: monospace; font-size: 0.8125rem;">
                                {{ $category->slug }}
                            </td>
                            <td style="padding: 14px 18px; text-align: center; font-weight: 600; color: #C5A880;">
                                {{ $category->posts_count }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="padding: 30px; text-align: center; color: var(--mh-admin-muted);">
                                Chưa có chuyên mục nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Form tạo chuyên mục mới --}}
        <div style="background: #fff; padding: 24px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); height: fit-content;">
            <h3 style="margin: 0 0 16px 0; font-size: 1rem; font-weight: 700; color: var(--mh-admin-heading);">Thêm Chuyên Mục Mới</h3>
            
            <form method="POST" action="{{ route('admin.blog.categories.store') }}">
                @csrf
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 6px;">Tên Chuyên Mục <span style="color: red;">*</span></label>
                    <input type="text" name="name" required placeholder="Ví dụ: Cẩm Nang Lễ Nghi Cưới" style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 6px;">Đường Dẫn Slug (Tùy chọn)</label>
                    <input type="text" name="slug" placeholder="cam-nang-le-nghi-cuoi" style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 6px;">Mô Tả Chuyên Mục</label>
                    <textarea name="description" rows="3" placeholder="Mô tả tóm tắt ý nghĩa và định hướng nội dung của chuyên mục..." style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;"></textarea>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; font-size: 0.8125rem; margin-bottom: 6px;">Thứ Tự Sắp Xếp</label>
                    <input type="number" name="sort_order" value="0" min="0" style="width: 100px; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">
                </div>

                <button type="submit" style="width: 100%; padding: 12px; border-radius: 8px; background: #C5A880; color: #fff; border: none; font-size: 0.875rem; font-weight: 600; cursor: pointer;">
                    Lưu Chuyên Mục Mới
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

