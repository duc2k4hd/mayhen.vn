@extends('admin.layouts.master')

@section('title', 'Quản Lý Mẫu Thiệp — Mây Hẹn')
@section('page_title', 'Danh Sách Mẫu Thiệp Cưới')

@section('content')
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_title">
            <span>Kho Mẫu Thiệp ({{ $templates->count() }} mẫu)</span>
            <a href="{{ route('templates.index') }}" class="mayhen_admin_btn_sm" target="_blank">Xem Thư Viện Public &rarr;</a>
        </div>

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
                            <img src="{{ $tpl->thumbnail_url }}" alt="{{ $tpl->name }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">
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
                                <button type="submit" class="mayhen_admin_btn_sm" style="font-size: 0.75rem;">
                                    {{ $tpl->is_featured ? '★ Nổi Bật' : '☆ Thường' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            @if ($tpl->is_active)
                                <span class="mayhen_admin_badge_paid">Đang Bật</span>
                            @else
                                <span class="mayhen_admin_badge_pending">Đã Tắt</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <form action="{{ route('admin.templates.toggle_active', $tpl->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="mayhen_admin_btn_sm">
                                        {{ $tpl->is_active ? 'Ẩn Mẫu' : 'Hiện Mẫu' }}
                                    </button>
                                </form>
                                <a href="{{ route('templates.preview', $tpl->slug) }}" target="_blank" class="mayhen_admin_btn_sm">
                                    Xem Thử
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
