@extends('admin.layouts.master')

@section('title', 'Quản Lý Bài Viết Cẩm Nang')

@section('content')
<div class="mayhen_admin_page">
    {{-- Header --}}
    <div class="mayhen_admin_page_header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <div class="mayhen_admin_breadcrumb" style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 4px;">
                <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: none;">Tổng quan</a> / <span>Cẩm nang cưới</span>
            </div>
            <h1 class="mayhen_admin_page_title" style="font-size: 1.5rem; font-weight: 700; margin: 0; color: var(--mh-admin-heading);">
                Quản Lý Bài Viết &amp; Cẩm Nang Cưới
            </h1>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.blog.categories') }}" class="mayhen_admin_btn mayhen_admin_btn_outline" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px; border: 1px solid var(--mh-admin-border); background: #fff; text-decoration: none; font-size: 0.875rem; color: var(--mh-admin-text);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
                <span>Chuyên Mục</span>
            </a>
            <a href="{{ route('admin.blog.create') }}" class="mayhen_admin_btn mayhen_admin_btn_primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 8px; background: #C5A880; color: #fff; text-decoration: none; font-size: 0.875rem; font-weight: 600;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Soạn Bài Mới</span>
            </a>
        </div>
    </div>

    {{-- Flash Message --}}
    @if(session('success'))
        <div style="padding: 14px 18px; border-radius: 8px; background: #E8F5E9; border: 1px solid #C8E6C9; color: #2E7D32; margin-bottom: 20px; font-size: 0.875rem;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Thống kê KPI --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--mh-admin-muted); font-weight: 600;">Tổng Số Bài Viết</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--mh-admin-heading); margin-top: 6px;">{{ number_format($stats['total']) }}</div>
        </div>
        <div style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: #2E7D32; font-weight: 600;">Đã Xuất Bản (Live)</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #2E7D32; margin-top: 6px;">{{ number_format($stats['published']) }}</div>
        </div>
        <div style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: #E65100; font-weight: 600;">Bản Nháp (Draft)</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #E65100; margin-top: 6px;">{{ number_format($stats['draft']) }}</div>
        </div>
        <div style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--mh-admin-muted); font-weight: 600;">Tổng Lượt Đọc</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #9C7A4A; margin-top: 6px;">{{ number_format($stats['total_views']) }}</div>
        </div>
    </div>

    {{-- Bộ Lọc --}}
    <div style="background: #fff; padding: 16px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); margin-bottom: 24px;">
        <form method="GET" action="{{ route('admin.blog.index') }}" style="display: flex; flex-wrap: wrap; gap: 14px; align-items: center;">
            <div style="flex: 1; min-width: 240px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tiêu đề, tác giả..." style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem;">
            </div>
            <div style="width: 220px;">
                <select name="category_id" style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem; background: #fff;">
                    <option value="">-- Tất Cả Chuyên Mục --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width: 160px;">
                <select name="status" style="width: 100%; padding: 10px 14px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.875rem; background: #fff;">
                    <option value="">-- Trạng Thái --</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Đã xuất bản</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Bản nháp</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Hẹn giờ</option>
                </select>
            </div>
            <button type="submit" style="padding: 10px 18px; border-radius: 8px; background: #1F1D1A; color: #fff; border: none; font-size: 0.875rem; font-weight: 500; cursor: pointer;">
                Lọc Bài Viết
            </button>
            @if(request()->anyFilled(['search', 'category_id', 'status']))
                <a href="{{ route('admin.blog.index') }}" style="font-size: 0.875rem; color: var(--mh-admin-muted); text-decoration: none; padding: 10px 8px;">Đặt lại</a>
            @endif
        </form>
    </div>

    {{-- Bảng Bài Viết --}}
    <div style="background: #fff; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
            <thead>
                <tr style="background: #FAF8F5; border-bottom: 1px solid var(--mh-admin-border); color: var(--mh-admin-muted); font-size: 0.75rem; text-transform: uppercase;">
                    <th style="padding: 14px 18px;">Bài Viết</th>
                    <th style="padding: 14px 18px;">Chuyên Mục</th>
                    <th style="padding: 14px 18px;">Tác Giả</th>
                    <th style="padding: 14px 18px;">Lượt Đọc</th>
                    <th style="padding: 14px 18px;">Trạng Thái</th>
                    <th style="padding: 14px 18px;">Ngày Đăng</th>
                    <th style="padding: 14px 18px; text-align: right;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    <tr style="border-bottom: 1px solid var(--mh-admin-border-subtle);">
                        <td style="padding: 14px 18px;">
                            <div style="display: flex; gap: 12px; align-items: center;">
                                @if($post->cover_image_url)
                                    <img src="{{ $post->cover_image_url }}" alt="" style="width: 52px; height: 38px; object-fit: cover; border-radius: 6px; border: 1px solid #eee;">
                                @endif
                                <div>
                                    <a href="{{ route('admin.blog.edit', $post->id) }}" style="font-weight: 600; color: var(--mh-admin-heading); text-decoration: none;">
                                        {{ $post->title }}
                                    </a>
                                    @if($post->is_featured)
                                        <span style="display: inline-block; padding: 2px 6px; border-radius: 4px; background: #FFF8E1; color: #F57F17; font-size: 0.6875rem; font-weight: 600; margin-left: 6px;">Nổi bật</span>
                                    @endif
                                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 3px;">
                                        {{ $post->reading_time_minutes }} phút đọc &bull; 
                                        <a href="{{ route('blog.show', $post->slug) }}" target="_blank" style="color: #9C7A4A; text-decoration: none;">Xem thử ngoài web &nearr;</a>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 14px 18px; color: var(--mh-admin-text);">
                            {{ $post->category?->name ?? 'Chưa phân loại' }}
                        </td>
                        <td style="padding: 14px 18px; color: var(--mh-admin-muted);">
                            {{ $post->author_name }}
                        </td>
                        <td style="padding: 14px 18px; font-weight: 600; color: var(--mh-admin-heading);">
                            {{ number_format($post->view_count) }}
                        </td>
                        <td style="padding: 14px 18px;">
                            @if($post->status === 'published')
                                <span style="display: inline-block; padding: 4px 10px; border-radius: 20px; background: #E8F5E9; color: #2E7D32; font-size: 0.75rem; font-weight: 600;">Xuất bản</span>
                            @elseif($post->status === 'draft')
                                <span style="display: inline-block; padding: 4px 10px; border-radius: 20px; background: #FFF3E0; color: #E65100; font-size: 0.75rem; font-weight: 600;">Bản nháp</span>
                            @else
                                <span style="display: inline-block; padding: 4px 10px; border-radius: 20px; background: #ECEFF1; color: #455A64; font-size: 0.75rem; font-weight: 600;">{{ ucfirst($post->status) }}</span>
                            @endif
                        </td>
                        <td style="padding: 14px 18px; color: var(--mh-admin-muted); font-size: 0.8125rem;">
                            {{ $post->formatted_published_date }}
                        </td>
                        <td style="padding: 14px 18px; text-align: right;">
                            <div style="display: inline-flex; gap: 8px;">
                                <a href="{{ route('admin.blog.edit', $post->id) }}" style="padding: 6px 10px; border-radius: 6px; border: 1px solid var(--mh-admin-border); background: #fff; color: var(--mh-admin-text); text-decoration: none; font-size: 0.8125rem;">Sửa</a>
                                <form method="POST" action="{{ route('admin.blog.destroy', $post->id) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="padding: 6px 10px; border-radius: 6px; border: 1px solid #FFCDD2; background: #FFEBEE; color: #C62828; font-size: 0.8125rem; cursor: pointer;">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 40px; text-align: center; color: var(--mh-admin-muted);">
                            Chưa có bài viết nào phù hợp với điều kiện lọc.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($posts->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--mh-admin-border);">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

