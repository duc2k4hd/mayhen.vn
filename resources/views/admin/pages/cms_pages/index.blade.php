@extends('admin.layouts.master')

@section('title', 'Quản Lý Trang Nội Dung Tĩnh')

@section('content')
<div class="mayhen_admin_page">
    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <div class="mayhen_admin_breadcrumb" style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 4px;">
                <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: none;">Tổng quan</a> / <span>Trang nội dung tĩnh</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin: 0; color: var(--mh-admin-heading);">
                Quản Lý Trang Nội Dung Tĩnh &amp; Pháp Lý
            </h1>
        </div>
        <a href="{{ route('admin.pages.create') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 8px; background: #C5A880; color: #fff; text-decoration: none; font-size: 0.875rem; font-weight: 600;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Tạo Trang Mới</span>
        </a>
    </div>

    {{-- Flash Message --}}
    @if(session('success'))
        <div style="padding: 14px 18px; border-radius: 8px; background: #E8F5E9; border: 1px solid #C8E6C9; color: #2E7D32; margin-bottom: 20px; font-size: 0.875rem;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="padding: 14px 18px; border-radius: 8px; background: #FFEBEE; border: 1px solid #FFCDD2; color: #C62828; margin-bottom: 20px; font-size: 0.875rem;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Thống kê KPI --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--mh-admin-muted); font-weight: 600;">Tổng Số Trang</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--mh-admin-heading); margin-top: 6px;">{{ $stats['total'] }}</div>
        </div>
        <div style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: #9C7A4A; font-weight: 600;">Trang Cốt Lõi Hệ Thống</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #9C7A4A; margin-top: 6px;">{{ $stats['system'] }}</div>
        </div>
        <div style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: #2E7D32; font-weight: 600;">Đang Xuất Bản (Live)</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #2E7D32; margin-top: 6px;">{{ $stats['published'] }}</div>
        </div>
        <div style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--mh-admin-muted); font-weight: 600;">Trang Tùy Chỉnh</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--mh-admin-heading); margin-top: 6px;">{{ $stats['custom'] }}</div>
        </div>
    </div>

    {{-- Bảng Danh Sách Trang --}}
    <div style="background: #fff; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
            <thead>
                <tr style="background: #FAF8F5; border-bottom: 1px solid var(--mh-admin-border); color: var(--mh-admin-muted); font-size: 0.75rem; text-transform: uppercase;">
                    <th style="padding: 14px 18px;">Tên Trang &amp; Tiêu Đề</th>
                    <th style="padding: 14px 18px;">Đường Dẫn (Slug)</th>
                    <th style="padding: 14px 18px;">Phân Loại</th>
                    <th style="padding: 14px 18px;">Trạng Thái</th>
                    <th style="padding: 14px 18px;">Cập Nhật Lần Cuối</th>
                    <th style="padding: 14px 18px; text-align: right;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $page)
                    @php
                        // Xác định đường dẫn xem ngoài web
                        $publicUrl = match($page->slug) {
                            'dieu-khoan-dich-vu' => route('terms'),
                            'chinh-sach-bao-mat' => route('privacy'),
                            've-chung-toi' => route('about'),
                            default => route('page.show', $page->slug),
                        };
                    @endphp
                    <tr style="border-bottom: 1px solid var(--mh-admin-border-subtle);">
                        <td style="padding: 14px 18px;">
                            <a href="{{ route('admin.pages.edit', $page->id) }}" style="font-weight: 600; color: var(--mh-admin-heading); text-decoration: none;">
                                {{ $page->title }}
                            </a>
                            @if($page->lead_summary)
                                <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 3px;">
                                    {{ Str::limit($page->lead_summary, 80) }}
                                </div>
                            @endif
                        </td>
                        <td style="padding: 14px 18px; font-size: 0.8125rem; color: #666;">
                            /{{ $page->slug }}
                        </td>
                        <td style="padding: 14px 18px;">
                            @if($page->is_system)
                                <span style="display: inline-block; padding: 3px 8px; border-radius: 4px; background: #FFF8E1; color: #F57F17; font-size: 0.75rem; font-weight: 600;">Hệ thống</span>
                            @else
                                <span style="display: inline-block; padding: 3px 8px; border-radius: 4px; background: #ECEFF1; color: #455A64; font-size: 0.75rem; font-weight: 600;">Tùy chỉnh</span>
                            @endif
                        </td>
                        <td style="padding: 14px 18px;">
                            @if($page->is_published)
                                <span style="display: inline-block; padding: 4px 10px; border-radius: 20px; background: #E8F5E9; color: #2E7D32; font-size: 0.75rem; font-weight: 600;">Live</span>
                            @else
                                <span style="display: inline-block; padding: 4px 10px; border-radius: 20px; background: #FFF3E0; color: #E65100; font-size: 0.75rem; font-weight: 600;">Ẩn</span>
                            @endif
                        </td>
                        <td style="padding: 14px 18px; color: var(--mh-admin-muted); font-size: 0.8125rem;">
                            {{ $page->formatted_updated_at }}
                            @if($page->updater)
                                <div style="font-size: 0.75rem; color: #999;">Bởi {{ $page->updater->name }}</div>
                            @endif
                        </td>
                        <td style="padding: 14px 18px; text-align: right;">
                            <div style="display: inline-flex; gap: 8px;">
                                <a href="{{ $publicUrl }}" target="_blank" style="padding: 6px 10px; border-radius: 6px; border: 1px solid #C5A880; background: #FFFDF9; color: #9C7A4A; text-decoration: none; font-size: 0.8125rem;">Xem &nearr;</a>
                                <a href="{{ route('admin.pages.edit', $page->id) }}" style="padding: 6px 10px; border-radius: 6px; border: 1px solid var(--mh-admin-border); background: #fff; color: var(--mh-admin-text); text-decoration: none; font-size: 0.8125rem;">Sửa</a>
                                @if(!$page->is_system)
                                    <form method="POST" action="{{ route('admin.pages.destroy', $page->id) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa trang này không?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="padding: 6px 10px; border-radius: 6px; border: 1px solid #FFCDD2; background: #FFEBEE; color: #C62828; font-size: 0.8125rem; cursor: pointer;">Xóa</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 40px; text-align: center; color: var(--mh-admin-muted);">
                            Chưa có trang nội dung nào.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

