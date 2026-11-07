@extends('admin.layouts.master')

@section('title', 'Quản Lý Mẫu Thiệp — Mây Hẹn')
@section('page_title', 'Danh Sách Mẫu Thiệp Cưới')

@section('content')
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header">
            <div class="mayhen_admin_card_title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M14.31 8l5.74 9.94M9.69 8h11.48M7.38 12l5.74-9.94M9.69 16L3.95 6.06M14.31 16H2.83m13.79-4l-5.74 9.94"></path>
                </svg>
                <span>Kho Mẫu Thiệp ({{ $templates->count() }} mẫu)</span>
            </div>
            <a href="{{ route('templates.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm" target="_blank">
                Xem Thư Viện Public &nearr;
            </a>
        </div>

        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Tên Mẫu</th>
                        <th>Slug</th>
                        <th>Phong Cách</th>
                        <th>Số Thiệp Đã Dùng</th>
                        <th>Nổi Bật</th>
                        <th>Trạng Thái</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($templates as $tpl)
                        <tr>
                            <td>
                                <img src="{{ $tpl->thumbnail_url }}" alt="{{ $tpl->name }}" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid var(--mh-admin-border);">
                            </td>
                            <td>
                                <strong>{{ $tpl->name }}</strong>
                            </td>
                            <td><code>{{ $tpl->slug }}</code></td>
                            <td>{{ $tpl->category_label }}</td>
                            <td><strong>{{ $tpl->invitations_count }}</strong> thiệp</td>
                            <td>
                                <form action="{{ route('admin.templates.toggle_featured', $tpl->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm" style="font-size: 0.75rem;">
                                        {{ $tpl->is_featured ? '★ Nổi Bật' : '☆ Thường' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                @if ($tpl->is_active)
                                    <span class="mayhen_admin_badge mayhen_admin_badge_success">Đang Bật</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge_warning">Đã Tắt</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <form action="{{ route('admin.templates.toggle_active', $tpl->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                                            {{ $tpl->is_active ? 'Ẩn Mẫu' : 'Hiện Mẫu' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('templates.preview', $tpl->slug) }}" target="_blank" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                                        Xem Thử
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
