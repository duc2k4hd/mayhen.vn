@extends('admin.layouts.master')

@section('title', 'Trạng Thái Dịch Vụ & Nhật Ký Lỗi Hệ Thống')

@section('content')
<div class="mayhen_admin_page_header">
    <div class="mayhen_admin_page_header_left">
        <h1 class="mayhen_admin_page_title">Trạng Thái Dịch Vụ & Nhật Ký Lỗi Hệ Thống</h1>
        <p class="mayhen_admin_page_desc">
            Bảng điều khiển theo dõi sức khỏe hạ tầng, tài nguyên máy chủ và thanh tra nhật ký lỗi thời gian thực theo chuẩn Mục 158 PRD V3.
        </p>
    </div>
    <div class="mayhen_admin_page_header_actions">
        <a href="{{ route('admin.system.health.download_log') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Tải Tệp Log (.log)
        </a>

        @if($currentTab === 'error-logs')
            <form action="{{ route('admin.system.health.clear_log') }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn làm sạch toàn bộ tệp nhật ký lỗi laravel.log? Hành động này sẽ được ghi nhận kiểm toán.');">
                @csrf
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="color: #e53e3e;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    Xóa Tệp Log
                </button>
            </form>
        @endif
    </div>
</div>

{{-- 4 Thẻ KPI Sức Khỏe Hạ Tầng --}}
<div class="mayhen_admin_kpi_grid" style="margin-bottom: 24px;">
    <div class="mayhen_admin_kpi_card">
        <div class="mayhen_admin_kpi_icon" style="background: {{ $kpis['overall_status'] === 'healthy' ? 'rgba(45, 106, 79, 0.12)' : 'rgba(217, 83, 79, 0.12)' }}; color: {{ $kpis['overall_status'] === 'healthy' ? '#2D6A4F' : '#C53030' }};">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
        </div>
        <div class="mayhen_admin_kpi_content">
            <div class="mayhen_admin_kpi_label">Tình Trạng Dịch Vụ</div>
            <div class="mayhen_admin_kpi_value" style="font-size: 18px; font-weight: 600; color: {{ $kpis['overall_status'] === 'healthy' ? '#2D6A4F' : '#C53030' }};">
                {{ $kpis['service_status'] }}
            </div>
            <div class="mayhen_admin_kpi_sub">Tất cả subsystem chính</div>
        </div>
    </div>

    <div class="mayhen_admin_kpi_card">
        <div class="mayhen_admin_kpi_icon" style="background: {{ $kpis['errors_today_count'] > 0 ? 'rgba(217, 83, 79, 0.12)' : 'rgba(108, 117, 125, 0.12)' }}; color: {{ $kpis['errors_today_count'] > 0 ? '#C53030' : '#6C757D' }};">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
            </svg>
        </div>
        <div class="mayhen_admin_kpi_content">
            <div class="mayhen_admin_kpi_label">Lỗi Hệ Thống Hôm Nay</div>
            <div class="mayhen_admin_kpi_value" style="color: {{ $kpis['errors_today_count'] > 0 ? '#C53030' : 'inherit' }};">
                {{ number_format($kpis['errors_today_count']) }}
            </div>
            <div class="mayhen_admin_kpi_sub">Ghi nhận trong laravel.log</div>
        </div>
    </div>

    <div class="mayhen_admin_kpi_card">
        <div class="mayhen_admin_kpi_icon" style="background: rgba(197, 168, 128, 0.15); color: #9C7A4A;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                <line x1="6" y1="6" x2="6.01" y2="6"></line>
                <line x1="6" y1="18" x2="6.01" y2="18"></line>
            </svg>
        </div>
        <div class="mayhen_admin_kpi_content">
            <div class="mayhen_admin_kpi_label">Bộ Nhớ PHP Sử Dụng</div>
            <div class="mayhen_admin_kpi_value">{{ $kpis['memory_usage_mb'] }} MB</div>
            <div class="mayhen_admin_kpi_sub">Đỉnh điểm: {{ $kpis['memory_peak_mb'] }} MB</div>
        </div>
    </div>

    <div class="mayhen_admin_kpi_card">
        <div class="mayhen_admin_kpi_icon" style="background: rgba(74, 144, 226, 0.12); color: #2B6CB0;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
        </div>
        <div class="mayhen_admin_kpi_content">
            <div class="mayhen_admin_kpi_label">Không Gian Lưu Trữ Đĩa</div>
            <div class="mayhen_admin_kpi_value">{{ $kpis['disk_free_gb'] }} GB</div>
            <div class="mayhen_admin_kpi_sub">Đang sử dụng {{ $kpis['disk_used_percent'] }}%</div>
        </div>
    </div>
</div>

{{-- Navigation Tabs --}}
<div class="mayhen_admin_tabs" style="display: flex; gap: 8px; border-bottom: 1px solid #EFE8DD; margin-bottom: 24px;">
    <a href="{{ route('admin.system.health.index', ['tab' => 'services-health']) }}" 
       class="mayhen_admin_tab {{ $currentTab === 'services-health' ? 'active' : '' }}"
       style="padding: 10px 18px; font-size: 14px; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'services-health' ? '#C5A880' : 'transparent' }}; color: {{ $currentTab === 'services-health' ? '#1F1D1A' : '#6c757d' }};">
        🩺 Trạng Thái Các Dịch Vụ
    </a>
    <a href="{{ route('admin.system.health.index', ['tab' => 'error-logs']) }}" 
       class="mayhen_admin_tab {{ $currentTab === 'error-logs' ? 'active' : '' }}"
       style="padding: 10px 18px; font-size: 14px; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'error-logs' ? '#C5A880' : 'transparent' }}; color: {{ $currentTab === 'error-logs' ? '#1F1D1A' : '#6c757d' }};">
        📜 Nhật Ký Lỗi Hệ Thống (Log Viewer)
    </a>
    <a href="{{ route('admin.system.health.index', ['tab' => 'server-metrics']) }}" 
       class="mayhen_admin_tab {{ $currentTab === 'server-metrics' ? 'active' : '' }}"
       style="padding: 10px 18px; font-size: 14px; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'server-metrics' ? '#C5A880' : 'transparent' }}; color: {{ $currentTab === 'server-metrics' ? '#1F1D1A' : '#6c757d' }};">
        ⚙️ Chỉ Số Tài Nguyên Runtime
    </a>
</div>

{{-- TAB 1: TRẠNG THÁI CÁC DỊCH VỤ --}}
@if($currentTab === 'services-health')
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
        {{-- Database Health --}}
        <div class="mayhen_admin_card" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(45, 106, 79, 0.12); color: #2D6A4F; display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                            <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                            <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 15px; color: #1F1D1A; font-weight: 600;">Cơ Sở Dữ Liệu</h4>
                        <span style="font-size: 12px; color: #8A8175;">Driver: {{ $healthSummary['database']['driver'] }}</span>
                    </div>
                </div>
                <span class="mayhen_admin_badge mayhen_admin_badge_success">Kết nối tốt</span>
            </div>
            <div style="font-size: 13px; color: #4A453E; line-height: 1.6;">
                <div>Độ trễ phản hồi truy vấn: <strong>{{ $healthSummary['database']['latency_ms'] }} ms</strong></div>
                <div style="color: #2D6A4F; font-size: 12px; margin-top: 4px;">✓ Đọc & Ghi dữ liệu bình thường</div>
            </div>
        </div>

        {{-- Cache Health --}}
        <div class="mayhen_admin_card" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(197, 168, 128, 0.15); color: #9C7A4A; display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                        </svg>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 15px; color: #1F1D1A; font-weight: 600;">Bộ Nhớ Đệm (Cache)</h4>
                        <span style="font-size: 12px; color: #8A8175;">Driver: {{ $healthSummary['cache']['driver'] }}</span>
                    </div>
                </div>
                <span class="mayhen_admin_badge mayhen_admin_badge_success">Sẵn sàng</span>
            </div>
            <div style="font-size: 13px; color: #4A453E; line-height: 1.6;">
                <div>Độ trễ Read/Write: <strong>{{ $healthSummary['cache']['latency_ms'] }} ms</strong></div>
                <div style="color: #2D6A4F; font-size: 12px; margin-top: 4px;">✓ Cơ chế cache tránh query lặp hoạt động tối ưu</div>
            </div>
        </div>

        {{-- Storage Health --}}
        <div class="mayhen_admin_card" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(74, 144, 226, 0.12); color: #2B6CB0; display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                            <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                        </svg>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 15px; color: #1F1D1A; font-weight: 600;">Lưu Trữ & Thư Mục</h4>
                        <span style="font-size: 12px; color: #8A8175;">Quyền ghi: {{ $healthSummary['storage']['is_writable'] ? 'Có (Writable)' : 'Bị Khóa' }}</span>
                    </div>
                </div>
                <span class="mayhen_admin_badge mayhen_admin_badge_{{ $healthSummary['storage']['status'] === 'healthy' ? 'success' : 'warning' }}">
                    {{ $healthSummary['storage']['status'] === 'healthy' ? 'An Toàn' : 'Cảnh Báo' }}
                </span>
            </div>
            <div style="font-size: 13px; color: #4A453E; line-height: 1.6;">
                <div>Trống: <strong>{{ $healthSummary['storage']['free_gb'] }} GB</strong> / {{ $healthSummary['storage']['total_gb'] }} GB</div>
                <div>Kích thước tệp log hiện tại: <strong>{{ $healthSummary['storage']['log_size_mb'] }} MB</strong></div>
            </div>
        </div>

        {{-- Queue Worker --}}
        <div class="mayhen_admin_card" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(45, 106, 79, 0.12); color: #2D6A4F; display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                        </svg>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 15px; color: #1F1D1A; font-weight: 600;">Hàng Đợi Nền (Queue)</h4>
                        <span style="font-size: 12px; color: #8A8175;">Driver: {{ $healthSummary['queue']['driver'] }}</span>
                    </div>
                </div>
                <span class="mayhen_admin_badge mayhen_admin_badge_{{ $healthSummary['queue']['failed_jobs'] > 0 ? 'warning' : 'success' }}">
                    {{ $healthSummary['queue']['failed_jobs'] > 0 ? $healthSummary['queue']['failed_jobs'].' Job Lỗi' : 'Bình Thường' }}
                </span>
            </div>
            <div style="font-size: 13px; color: #4A453E; line-height: 1.6;">
                <div>Tác vụ đang chờ (Pending): <strong>{{ $healthSummary['queue']['pending_jobs'] }}</strong></div>
                <div>Tác vụ thất bại (Failed): <strong>{{ $healthSummary['queue']['failed_jobs'] }}</strong></div>
            </div>
        </div>
    </div>
@endif

{{-- TAB 2: NHẬT KÝ LỖI HỆ THỐNG --}}
@if($currentTab === 'error-logs')
    <div class="mayhen_admin_card">
        {{-- Bộ lọc Logs --}}
        <div style="padding: 16px 20px; border-bottom: 1px solid #EFE8DD;">
            <form action="{{ route('admin.system.health.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="tab" value="error-logs">
                
                <div style="flex: 1; min-width: 240px;">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm theo thông điệp lỗi, Exception class, tên file..." 
                           class="mayhen_admin_form_input" style="height: 38px;">
                </div>

                <div style="width: 170px;">
                    <select name="level" class="mayhen_admin_form_select" style="height: 38px;">
                        <option value="">Tất cả cấp độ</option>
                        <option value="ERROR" {{ request('level') === 'ERROR' ? 'selected' : '' }}>ERROR (Lỗi)</option>
                        <option value="CRITICAL" {{ request('level') === 'CRITICAL' ? 'selected' : '' }}>CRITICAL (Nghiêm trọng)</option>
                        <option value="WARNING" {{ request('level') === 'WARNING' ? 'selected' : '' }}>WARNING (Cảnh báo)</option>
                        <option value="INFO" {{ request('level') === 'INFO' ? 'selected' : '' }}>INFO (Thông tin)</option>
                    </select>
                </div>

                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_secondary" style="height: 38px;">
                    Lọc Nhật Ký
                </button>

                @if(request()->anyFilled(['search', 'level']))
                    <a href="{{ route('admin.system.health.index', ['tab' => 'error-logs']) }}" class="mayhen_admin_btn mayhen_admin_btn_outline" style="height: 38px;">
                        Xóa lọc
                    </a>
                @endif
            </form>
        </div>

        {{-- Bảng hiển thị logs --}}
        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th style="width: 170px;">Thời Gian</th>
                        <th style="width: 110px;">Cấp Độ</th>
                        <th>Thông Điệp Lỗi (Message)</th>
                        <th style="width: 110px; text-align: right;">Chi Tiết</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $idx => $log)
                        <tr>
                            <td>
                                <div style="font-size: 13px; font-weight: 500; color: #1F1D1A;">{{ $log['timestamp'] }}</div>
                                <div style="font-size: 11px; color: #8A8175;">Env: {{ $log['environment'] }}</div>
                            </td>
                            <td>
                                <span class="mayhen_admin_badge mayhen_admin_badge_{{ $log['badge_color'] }}">
                                    {{ $log['level'] }}
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 13px; font-weight: 600; color: #1F1D1A; word-break: break-all;">
                                    {{ $log['message'] }}
                                </div>
                                @if($log['stack_trace'])
                                    <div id="trace-{{ $idx }}" style="display: none; margin-top: 10px; background: #2C2824; color: #FAF8F5; padding: 12px; border-radius: 6px; font-size: 11px; max-height: 250px; overflow-y: auto; white-space: pre-wrap;">
                                        {{ $log['stack_trace'] }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if($log['stack_trace'])
                                    <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" style="padding: 4px 8px; font-size: 11px;" onclick="toggleTrace({{ $idx }})">
                                        Xem Trace
                                    </button>
                                @else
                                    <span style="font-size: 11px; color: #8A8175;">Không có trace</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 48px; color: #8A8175;">
                                <p style="margin: 0; font-size: 14px;">Không tìm thấy bản ghi lỗi nào phù hợp trong tệp log.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- TAB 3: CHỈ SỐ TÀI NGUYÊN RUNTIME --}}
@if($currentTab === 'server-metrics')
    <div class="mayhen_admin_card" style="max-width: 800px;">
        <div style="padding: 20px 24px; border-bottom: 1px solid #EFE8DD;">
            <h3 style="margin: 0 0 6px 0; font-size: 16px; color: #1F1D1A; font-weight: 600;">
                Thông Số Môi Trường Máy Chủ & PHP Runtime
            </h3>
            <p style="margin: 0; font-size: 13px; color: #8A8175;">
                Các chỉ số cấu hình thực tế của PHP Engine và phần mở rộng phục vụ nền tảng Mây Hẹn Atelier.
            </p>
        </div>

        <div style="padding: 24px;">
            <table class="mayhen_admin_table">
                <tbody>
                    <tr>
                        <td style="width: 250px; font-weight: 600; color: #1F1D1A;">Phiên Bản PHP</td>
                        <td><span class="mayhen_admin_badge mayhen_admin_badge_info">PHP {{ $serverMetrics['php_version'] }} ({{ $serverMetrics['sapi'] }})</span></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #1F1D1A;">Phiên Bản Laravel Framework</td>
                        <td><strong>Laravel {{ $healthSummary['environment']['laravel_version'] }}</strong></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #1F1D1A;">Giới Hạn Bộ Nhớ (memory_limit)</td>
                        <td><strong>{{ $serverMetrics['memory_limit'] }}</strong></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #1F1D1A;">Thời Gian Thực Thi Tối Đa (max_execution_time)</td>
                        <td><strong>{{ $serverMetrics['max_execution_time'] }}</strong></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #1F1D1A;">Kích Thước Upload Tối Đa (upload_max_filesize)</td>
                        <td><strong>{{ $serverMetrics['upload_max_filesize'] }}</strong> (Post Max: {{ $serverMetrics['post_max_size'] }})</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #1F1D1A;">Múi Giờ Hệ Thống (Timezone)</td>
                        <td>{{ $healthSummary['environment']['timezone'] }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #1F1D1A;">OPcache Tăng Tốc PHP</td>
                        <td>
                            @if($healthSummary['environment']['opcache_enabled'])
                                <span class="mayhen_admin_badge mayhen_admin_badge_success">Đang Kích Hoạt</span>
                            @else
                                <span class="mayhen_admin_badge mayhen_admin_badge_muted">Tắt</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #1F1D1A;">Phần Mở Rộng Cốt Lõi (Extensions)</td>
                        <td>
                            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                @foreach($serverMetrics['extensions'] as $ext => $loaded)
                                    <span class="mayhen_admin_badge mayhen_admin_badge_{{ $loaded ? 'success' : 'danger' }}" style="font-size: 11px;">
                                        {{ $ext }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endif

<script>
function toggleTrace(idx) {
    const el = document.getElementById('trace-' + idx);
    if (el) {
        el.style.display = el.style.display === 'none' ? 'block' : 'none';
    }
}
</script>
@endsection

