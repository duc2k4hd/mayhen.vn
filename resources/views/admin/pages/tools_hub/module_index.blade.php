@extends('admin.layouts.master')

@section('title', 'Quản Lý ' . $stats['label'])

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/admin/css/pages/tools-hub.css') }}">
@endpush

@section('content')
<div class="mayhen_admin_tools_wrap">
    <div class="mayhen_admin_tools_head">
        <div>
            <div style="font-size:12.5px;color:#64748B;margin-bottom:4px;">
                <a href="{{ route('admin.tools_hub.index') }}" style="color:#1E4D3B;font-weight:600;text-decoration:none;">← Trung Tâm 9 Công Cụ</a> / <span>{{ $stats['label'] }}</span>
            </div>
            <h1 class="mayhen_admin_tools_title">Quản Lý {{ $stats['label'] }}</h1>
            <p class="mayhen_admin_tools_sub">Theo dõi toàn bộ bản ghi người dùng tạo ra, xem chi tiết dữ liệu, thu hồi link chia sẻ, xuất CSV và kiểm soát hạn lưu trữ 6 tháng.</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ $stats['public_url'] }}" target="_blank" rel="noopener" class="mayhen_admin_tools_btn mayhen_admin_tools_btn--outline">Mở Trang Công Khai ↗</a>
            <a href="{{ route('admin.tool_module.export', ['moduleSlug' => $moduleSlug]) }}" class="mayhen_admin_tools_btn mayhen_admin_tools_btn--primary">Xuất CSV {{ $stats['short'] }}</a>
        </div>
    </div>

    @if (session('success'))
        <div style="background:#DCFCE7;border:1px solid #86EFAC;color:#166534;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-weight:600;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div style="background:#FEE2E2;border:1px solid #FCA5A5;color:#991B1B;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-weight:600;">{{ session('error') }}</div>
    @endif

    <div class="mayhen_admin_tools_kpis">
        <div class="mayhen_admin_tools_kpi">
            <div class="mayhen_admin_tools_kpi_label">Tổng Bản Ghi Đang Sống</div>
            <div class="mayhen_admin_tools_kpi_val">{{ number_format($stats['total']) }}</div>
            <div style="font-size:11.5px;color:#1E4D3B;">+{{ number_format($stats['last_7_days']) }} trong 7 ngày qua</div>
        </div>
        <div class="mayhen_admin_tools_kpi">
            <div class="mayhen_admin_tools_kpi_label">Link Chia Sẻ Công Khai</div>
            <div class="mayhen_admin_tools_kpi_val">{{ number_format($stats['shared']) }}</div>
            <div style="font-size:11.5px;color:#64748B;">Hiệu lực tối đa 6 tháng</div>
        </div>
        <div class="mayhen_admin_tools_kpi">
            <div class="mayhen_admin_tools_kpi_label">Quá Hạn 6 Tháng</div>
            <div class="mayhen_admin_tools_kpi_val {{ $stats['expired'] > 0 ? 'mayhen_admin_tools_kpi_val--warn' : 'mayhen_admin_tools_kpi_val--ok' }}">{{ number_format($stats['expired']) }}</div>
            <div style="font-size:11.5px;color:#64748B;">Sắp hết hạn 30 ngày: {{ number_format($stats['expiring_soon']) }}</div>
        </div>
        <div class="mayhen_admin_tools_kpi">
            <div class="mayhen_admin_tools_kpi_label">Đã Lưu Trữ Phân Tích</div>
            <div class="mayhen_admin_tools_kpi_val mayhen_admin_tools_kpi_val--ok">{{ number_format($stats['archived_count']) }}</div>
            <div style="font-size:11.5px;color:#64748B;">Lưu vĩnh viễn trong archives</div>
        </div>
        <div class="mayhen_admin_tools_kpi">
            <div class="mayhen_admin_tools_kpi_label">Lượt Tương Tác (7 Ngày)</div>
            <div class="mayhen_admin_tools_kpi_val">{{ number_format($stats['activity_7d']) }}</div>
            <div style="font-size:11.5px;color:#64748B;">Ghi nhận từ Telemetry</div>
        </div>
    </div>

    <section class="mayhen_admin_tools_panel">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
            <form method="GET" action="{{ route('admin.tool_module.index', ['moduleSlug' => $moduleSlug]) }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm tiêu đề, slug, User ID..." style="padding:8px 12px;border:1px solid #CBD5E1;border-radius:8px;font-size:13px;min-width:240px;">
                <label style="font-size:12.5px;display:inline-flex;align-items:center;gap:5px;">
                    <input type="checkbox" name="expired_only" value="1" {{ request()->boolean('expired_only') ? 'checked' : '' }}> Chỉ xem quá hạn 6 tháng
                </label>
                <button type="submit" class="mayhen_admin_tools_btn mayhen_admin_tools_btn--primary">Tìm &amp; Lọc</button>
            </form>

            <form method="POST" action="{{ route('admin.tools_hub.prune') }}" onsubmit="return confirm('Chạy Cron lưu trữ phân tích & dọn bản ghi quá hạn 6 tháng cho riêng {{ $stats['label'] }}?');">
                @csrf
                <input type="hidden" name="months" value="6">
                <input type="hidden" name="dry_run" value="0">
                <input type="hidden" name="tool" value="{{ $toolKey }}">
                <button type="submit" class="mayhen_admin_tools_btn mayhen_admin_tools_btn--outline">Dọn Dữ Liệu Quá Hạn 6T Của Mục Này</button>
            </form>
        </div>

        <div class="mayhen_admin_tools_table_wrap">
            <table class="mayhen_admin_tools_table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tiêu đề / Slug</th>
                        <th>Người dùng &amp; Gói</th>
                        <th>Ngày tạo</th>
                        <th>Hạn 6 tháng</th>
                        <th>Chia sẻ</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recordsData['rows'] as $row)
                        <tr>
                            <td>#{{ $row['id'] }}</td>
                            <td>
                                <strong>{{ \Illuminate\Support\Str::limit($row['title'], 75) }}</strong>
                                @if ($row['slug'])
                                    <div style="font-size:11.5px;color:#64748B;">slug: {{ $row['slug'] }}</div>
                                @endif
                            </td>
                            <td>
                                <div><strong>{{ $row['user_name'] }}</strong> ({{ strtoupper($row['user_plan']) }})</div>
                                <div style="font-size:11.5px;color:#64748B;">{{ $row['user_email'] ?? ('User #' . ($row['user_id'] ?? 'guest')) }}</div>
                            </td>
                            <td>{{ $row['created_at'] }}</td>
                            <td>
                                @if ($row['is_expired'])
                                    <span class="mayhen_admin_tools_badge mayhen_admin_tools_badge--danger">Quá hạn ({{ $row['expires_at'] }})</span>
                                @elseif ($row['days_left'] !== null && $row['days_left'] <= 30)
                                    <span class="mayhen_admin_tools_badge mayhen_admin_tools_badge--warn">Còn {{ $row['days_left'] }} ngày</span>
                                @else
                                    <span class="mayhen_admin_tools_badge mayhen_admin_tools_badge--ok">{{ $row['expires_at'] }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($row['share_token'])
                                    <span class="mayhen_admin_tools_badge mayhen_admin_tools_badge--ok">Đang mở</span>
                                @else
                                    <span style="color:#94A3B8;font-size:12px;">Riêng tư</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                    <a href="{{ route('admin.tool_module.show', ['moduleSlug' => $moduleSlug, 'id' => $row['id']]) }}" class="mayhen_admin_tools_btn mayhen_admin_tools_btn--outline" style="padding:5px 10px;font-size:12px;">Xem Chi Tiết</a>
                                    @if ($row['share_token'])
                                        <form method="POST" action="{{ route('admin.tool_module.revoke_share', ['moduleSlug' => $moduleSlug, 'id' => $row['id']]) }}" onsubmit="return confirm('Thu hồi link chia sẻ công khai này?');">
                                            @csrf
                                            <button type="submit" class="mayhen_admin_tools_btn mayhen_admin_tools_btn--outline" style="padding:5px 10px;font-size:12px;">Thu hồi link</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.tool_module.destroy', ['moduleSlug' => $moduleSlug, 'id' => $row['id']]) }}" onsubmit="return confirm('Lưu tóm tắt phân tích vào archives và xóa bản ghi chi tiết này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="mayhen_admin_tools_btn mayhen_admin_tools_btn--danger" style="padding:5px 10px;font-size:12px;">Xóa &amp; Lưu PT</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;color:#64748B;padding:28px;">Chưa có bản ghi nào được tạo trong {{ $stats['label'] }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (! empty($recordsData['paginator']))
            <div style="margin-top:16px;">{{ $recordsData['paginator']->appends(request()->query())->links() }}</div>
        @endif
    </section>
</div>
@endsection
