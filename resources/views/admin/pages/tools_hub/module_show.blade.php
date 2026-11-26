@extends('admin.layouts.master')

@section('title', 'Chi Tiết #' . $detail['id'] . ' — ' . $detail['tool']['label'])

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/admin/css/pages/tools-hub.css') }}">
@endpush

@section('content')
<div class="mayhen_admin_tools_wrap">
    <div class="mayhen_admin_tools_head">
        <div>
            <div style="font-size:12.5px;color:#64748B;margin-bottom:4px;">
                <a href="{{ route('admin.tool_module.index', ['moduleSlug' => $moduleSlug]) }}" style="color:#1E4D3B;font-weight:600;text-decoration:none;">← Danh sách {{ $detail['tool']['label'] }}</a> / <span>Bản ghi #{{ $detail['id'] }}</span>
            </div>
            <h1 class="mayhen_admin_tools_title">Kiểm Định Chi Tiết Bản Ghi #{{ $detail['id'] }} ({{ $detail['tool']['short'] }})</h1>
            <p class="mayhen_admin_tools_sub">
                Chủ sở hữu: <strong>{{ $detail['user'] ? ($detail['user']->name . ' (' . $detail['user']->email . ' · ' . strtoupper($detail['user']->current_plan_slug ?? 'free') . ')') : 'Khách ẩn danh' }}</strong>
                · Tạo lúc: <strong>{{ $detail['created_at']?->format('d/m/Y H:i:s') }}</strong>
                · Hạn lưu 6 tháng: <strong>{{ $detail['expires_at']?->format('d/m/Y') }}</strong>
            </p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            @if ($detail['share_token'])
                <form method="POST" action="{{ route('admin.tool_module.revoke_share', ['moduleSlug' => $moduleSlug, 'id' => $detail['id']]) }}">
                    @csrf
                    <button type="submit" class="mayhen_admin_tools_btn mayhen_admin_tools_btn--outline">Thu Hồi Link Chia Sẻ</button>
                </form>
            @endif
            <form method="POST" action="{{ route('admin.tool_module.destroy', ['moduleSlug' => $moduleSlug, 'id' => $detail['id']]) }}" onsubmit="return confirm('Lưu bản phân tích vào archives và xóa bản ghi chi tiết này?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="mayhen_admin_tools_btn mayhen_admin_tools_btn--danger">Lưu Phân Tích &amp; Xóa Bản Ghi</button>
            </form>
        </div>
    </div>

    <section class="mayhen_admin_tools_panel" style="margin-bottom:20px;">
        <h2 style="margin:0 0 14px;font-size:17px;">Toàn Bộ Dữ Liệu Người Dùng Đã Tạo</h2>
        <div class="mayhen_admin_tools_table_wrap">
            <table class="mayhen_admin_tools_table">
                <thead><tr><th style="width:220px;">Trường dữ liệu</th><th>Giá trị</th></tr></thead>
                <tbody>
                    @foreach ($detail['fields'] as $col => $val)
                        <tr>
                            <td><code>{{ $col }}</code></td>
                            <td>
                                @if (is_string($val) && strlen($val) > 160)
                                    <pre style="white-space:pre-wrap;margin:0;max-height:260px;overflow:auto;background:#F8FAFC;padding:10px;border-radius:8px;font-size:12px;">{{ $val }}</pre>
                                @else
                                    {{ $val === null ? '—' : (is_bool($val) ? ($val ? 'true' : 'false') : (string) $val) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="mayhen_admin_tools_panel">
        <h2 style="margin:0 0 14px;font-size:17px;">Hoạt Động Gần Đây Của Tài Khoản Này Trên Hệ Thống</h2>
        <div class="mayhen_admin_tools_table_wrap">
            <table class="mayhen_admin_tools_table">
                <thead><tr><th>Thời gian</th><th>Công cụ</th><th>Hành động</th><th>Đường dẫn</th><th>IP</th></tr></thead>
                <tbody>
                    @forelse ($detail['recent_activities'] as $act)
                        <tr>
                            <td>{{ $act->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td><span class="mayhen_admin_tools_badge mayhen_admin_tools_badge--ok">{{ $act->tool_key }}</span></td>
                            <td><strong>{{ $act->method }}</strong> · {{ $act->action }}</td>
                            <td style="font-size:12px;color:#475569;">{{ $act->url }}</td>
                            <td>{{ $act->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="color:#64748B;text-align:center;padding:18px;">Chưa có log hành vi gần đây cho tài khoản này.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
