@extends('admin.layouts.master')

@section('title', 'Nhật Ký Quản Trị Hệ Thống — Mây Hẹn')
@section('page_title', 'Nhật Ký Quản Trị Hệ Thống (Audit Logs)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/settings.css') }}">
@endpush

@section('content')
    <!-- Intro Card -->
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 20px;">
        <h3 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; margin-bottom: 4px; color: var(--mh-admin-charcoal);">
            Nhật Ký Truy Vết Hành Động Quản Trị Viên
        </h3>
        <p style="color: var(--mh-admin-muted); font-size: 0.84375rem; margin: 0;">
            Ghi vết toàn bộ thao tác quan trọng trên hệ thống (duyệt đơn, đổi quyền người dùng, bật/ẩn thiệp, cập nhật bảng giá và tài khoản ngân hàng) để đảm bảo tính minh bạch và an toàn dữ liệu.
        </p>
    </div>

    <!-- Filter Bar -->
    <form action="{{ route('admin.settings.audit_logs') }}" method="GET" class="mayhen_admin_filter_bar" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 24px;">
        <div class="mayhen_admin_search_box" style="flex: 1; min-width: 260px; position: relative;">
            <input 
                type="text" 
                name="q" 
                value="{{ $currentSearch }}" 
                placeholder="Tìm tên admin, đối tượng, nội dung thao tác..." 
                class="mayhen_admin_input"
                style="width: 100%; padding-left: 36px;"
            >
            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--mh-admin-muted);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </span>
        </div>

        <select name="action" class="mayhen_admin_select" style="min-width: 180px;">
            <option value="">Tất cả loại hành động</option>
            @foreach($availableActions as $key => $lbl)
                <option value="{{ $key }}" {{ $currentAction === $key ? 'selected' : '' }}>
                    {{ $lbl }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            <span>Lọc Nhật Ký</span>
        </button>

        @if($currentSearch || $currentAction)
            <a href="{{ route('admin.settings.audit_logs') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                <span>Đặt lại</span>
            </a>
        @endif
    </form>

    <!-- Audit Logs Table Card -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div class="mayhen_admin_card_title">Danh Sách Nhật Ký Quản Trị</div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Hiển thị {{ count($logs) }} sự kiện gần nhất</div>
                <a href="{{ route('admin.settings.audit_logs.export', ['q' => $currentSearch, 'action' => $currentAction]) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>Xuất CSV (Excel)</span>
                </a>
            </div>
        </div>

        <div class="mayhen_admin_table_wrapper">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th style="width: 160px;">Thời Gian</th>
                        <th style="width: 170px;">Quản Trị Viên</th>
                        <th style="width: 140px; text-align: center;">Hành Động</th>
                        <th style="width: 130px;">Đối Tượng</th>
                        <th>Chi Tiết Thay Đổi</th>
                        <th style="width: 120px; text-align: right;">Địa Chỉ IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $actionName = $availableActions[$log['action'] ?? ''] ?? ($log['action'] ?? 'Thao tác');
                            $time = isset($log['timestamp']) ? \Carbon\Carbon::parse($log['timestamp'])->format('d/m/Y H:i:s') : '—';
                        @endphp
                        <tr>
                            <!-- Timestamp -->
                            <td style="font-size: 0.8125rem; color: var(--mh-admin-muted); font-family: monospace;">
                                {{ $time }}
                            </td>

                            <!-- Admin User -->
                            <td>
                                <div style="font-weight: 600; color: var(--mh-admin-charcoal);">
                                    {{ $log['admin_name'] ?? 'System' }}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">
                                    {{ $log['admin_email'] ?? '—' }}
                                </div>
                            </td>

                            <!-- Action Badge -->
                            <td style="text-align: center;">
                                @php
                                    $act = $log['action'] ?? '';
                                    $badgeClass = 'mayhen_admin_badge_muted';
                                    if (str_contains($act, 'approve') || str_contains($act, 'extend')) {
                                        $badgeClass = 'mayhen_admin_badge_success';
                                    } elseif (str_contains($act, 'cancel') || str_contains($act, 'reset')) {
                                        $badgeClass = 'mayhen_admin_badge_danger';
                                    } elseif (str_contains($act, 'update') || str_contains($act, 'role')) {
                                        $badgeClass = 'mayhen_admin_badge_warning';
                                    }
                                @endphp
                                <span class="mayhen_admin_badge {{ $badgeClass }}">
                                    {{ $actionName }}
                                </span>
                            </td>

                            <!-- Target -->
                            <td>
                                <span style="font-size: 0.8125rem; font-weight: 600; color: var(--mh-admin-gold);">
                                    {{ $log['target_type'] ?? 'Hệ thống' }}
                                </span>
                                @if(!empty($log['target_id']))
                                    <div style="font-size: 0.6875rem; color: var(--mh-admin-muted); font-family: monospace;">
                                        #{{ $log['target_id'] }}
                                    </div>
                                @endif
                            </td>

                            <!-- Description -->
                            <td>
                                <div style="font-size: 0.84375rem; color: var(--mh-admin-charcoal);">
                                    {{ $log['description'] ?? '—' }}
                                </div>
                            </td>

                            <!-- IP Address -->
                            <td style="text-align: right; font-size: 0.75rem; color: var(--mh-admin-muted); font-family: monospace;">
                                {{ $log['ip'] ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 48px; color: var(--mh-admin-muted);">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 12px; opacity: 0.5;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                                <div>Chưa có bản ghi nhật ký quản trị nào phù hợp.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

