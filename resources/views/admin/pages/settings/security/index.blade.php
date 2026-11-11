@extends('admin.layouts.master')

@section('title', 'Quản Trị An Ninh Mạng & Phòng Vệ Rate Limits')

@section('content')
<div class="mayhen_admin_page_header">
    <div class="mayhen_admin_page_header_left">
        <h1 class="mayhen_admin_page_title">Quản Trị An Ninh Mạng & Phòng Vệ Rate Limits</h1>
        <p class="mayhen_admin_page_desc">
            Trung tâm giám sát tường lửa, kiểm soát giới hạn tần suất và phòng vệ tấn công từ chối dịch vụ (DoS / Brute Force) theo Mục 177 PRD V3.
        </p>
    </div>
    <div class="mayhen_admin_page_header_actions">
        @if($currentTab === 'ip-rules')
            <button type="button" class="mayhen_admin_btn mayhen_admin_btn_primary" onclick="openModal('addIpRuleModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Thêm Quy Tắc IP Mới
            </button>
        @elseif($currentTab === 'rate-limits')
            <form action="{{ route('admin.settings.security.flush_cache') }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa toàn bộ bộ đệm Rate Limiter? Tất cả IP đang bị throttle tạm thời sẽ được gỡ bỏ ngay lập tức.');">
                @csrf
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="color: #9C7A4A;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="1 4 1 10 7 10"></polyline>
                        <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                    </svg>
                    Xóa Cache Rate Limiter
                </button>
            </form>
        @endif
    </div>
</div>

{{-- 4 Thẻ KPI Chỉ Số An Ninh --}}
<div class="mayhen_admin_kpi_grid" style="margin-bottom: 24px;">
    <div class="mayhen_admin_kpi_card">
        <div class="mayhen_admin_kpi_icon" style="background: rgba(217, 83, 79, 0.12); color: #C53030;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
            </svg>
        </div>
        <div class="mayhen_admin_kpi_content">
            <div class="mayhen_admin_kpi_label">Địa Chỉ IP Đang Chặn</div>
            <div class="mayhen_admin_kpi_value" style="color: {{ $kpis['active_blacklist_count'] > 0 ? '#C53030' : 'inherit' }};">
                {{ number_format($kpis['active_blacklist_count']) }}
            </div>
            <div class="mayhen_admin_kpi_sub">Trong danh sách Sổ đen (Blacklist)</div>
        </div>
    </div>

    <div class="mayhen_admin_kpi_card">
        <div class="mayhen_admin_kpi_icon" style="background: rgba(45, 106, 79, 0.12); color: #2D6A4F;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
        </div>
        <div class="mayhen_admin_kpi_content">
            <div class="mayhen_admin_kpi_label">Địa Chỉ IP Tin Cậy</div>
            <div class="mayhen_admin_kpi_value">{{ number_format($kpis['whitelist_count']) }}</div>
            <div class="mayhen_admin_kpi_sub" style="color: #2D6A4F;">Bỏ qua giới hạn tường lửa</div>
        </div>
    </div>

    <div class="mayhen_admin_kpi_card">
        <div class="mayhen_admin_kpi_icon" style="background: rgba(197, 168, 128, 0.15); color: #9C7A4A;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
            </svg>
        </div>
        <div class="mayhen_admin_kpi_content">
            <div class="mayhen_admin_kpi_label">Đe Dọa Phát Hiện (24h)</div>
            <div class="mayhen_admin_kpi_value">{{ number_format($kpis['threats_24h_count']) }}</div>
            <div class="mayhen_admin_kpi_sub">Vi phạm tần suất hoặc dò mật khẩu</div>
        </div>
    </div>

    <div class="mayhen_admin_kpi_card">
        <div class="mayhen_admin_kpi_icon" style="background: {{ $kpis['strict_mode_enabled'] ? 'rgba(217, 83, 79, 0.15)' : 'rgba(108, 117, 125, 0.12)' }}; color: {{ $kpis['strict_mode_enabled'] ? '#C53030' : '#6C757D' }};">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>
        <div class="mayhen_admin_kpi_content">
            <div class="mayhen_admin_kpi_label">Chế Độ Phòng Vệ Cao</div>
            <div class="mayhen_admin_kpi_value" style="font-size: 16px; font-weight: 600; color: {{ $kpis['strict_mode_enabled'] ? '#C53030' : '#2D6A4F' }};">
                {{ $kpis['strict_mode_enabled'] ? 'ĐANG KÍCH HOẠT' : 'TIÊU CHUẨN' }}
            </div>
            <div class="mayhen_admin_kpi_sub">{{ $kpis['strict_mode_enabled'] ? 'Ngưỡng siết chặt 50%' : 'Hoạt động bình thường' }}</div>
        </div>
    </div>
</div>

{{-- Navigation Tabs --}}
<div class="mayhen_admin_tabs" style="display: flex; gap: 8px; border-bottom: 1px solid #EFE8DD; margin-bottom: 24px;">
    <a href="{{ route('admin.settings.security.index', ['tab' => 'ip-rules']) }}" 
       class="mayhen_admin_tab {{ $currentTab === 'ip-rules' ? 'active' : '' }}"
       style="padding: 10px 18px; font-size: 14px; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'ip-rules' ? '#C5A880' : 'transparent' }}; color: {{ $currentTab === 'ip-rules' ? '#1F1D1A' : '#6c757d' }};">
        🛡️ Sổ Đen & Sổ Trắng IP ({{ number_format($kpis['active_blacklist_count'] + $kpis['whitelist_count']) }})
    </a>
    <a href="{{ route('admin.settings.security.index', ['tab' => 'rate-limits']) }}" 
       class="mayhen_admin_tab {{ $currentTab === 'rate-limits' ? 'active' : '' }}"
       style="padding: 10px 18px; font-size: 14px; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'rate-limits' ? '#C5A880' : 'transparent' }}; color: {{ $currentTab === 'rate-limits' ? '#1F1D1A' : '#6c757d' }};">
        ⚙️ Thiết Lập Ngưỡng Rate Limiting
    </a>
    <a href="{{ route('admin.settings.security.index', ['tab' => 'threat-logs']) }}" 
       class="mayhen_admin_tab {{ $currentTab === 'threat-logs' ? 'active' : '' }}"
       style="padding: 10px 18px; font-size: 14px; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'threat-logs' ? '#C5A880' : 'transparent' }}; color: {{ $currentTab === 'threat-logs' ? '#1F1D1A' : '#6c757d' }};">
        🚨 Nhật Ký Đe Dọa & Giám Sát ({{ number_format($kpis['threats_24h_count']) }})
    </a>
</div>

{{-- TAB 1: SỔ ĐEN & SỔ TRẮNG IP --}}
@if($currentTab === 'ip-rules')
    <div class="mayhen_admin_card">
        {{-- Bộ lọc IP --}}
        <div style="padding: 16px 20px; border-bottom: 1px solid #EFE8DD;">
            <form action="{{ route('admin.settings.security.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="tab" value="ip-rules">
                
                <div style="flex: 1; min-width: 240px;">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo địa chỉ IP hoặc lý do..." 
                           class="mayhen_admin_form_input" style="height: 38px;">
                </div>

                <div style="width: 180px;">
                    <select name="type" class="mayhen_admin_form_select" style="height: 38px;">
                        <option value="">Tất cả phân loại</option>
                        <option value="blacklist" {{ request('type') === 'blacklist' ? 'selected' : '' }}>Sổ đen (Blacklist)</option>
                        <option value="whitelist" {{ request('type') === 'whitelist' ? 'selected' : '' }}>Sổ trắng (Whitelist)</option>
                    </select>
                </div>

                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_secondary" style="height: 38px;">
                    Lọc Danh Sách
                </button>

                @if(request()->anyFilled(['search', 'type']))
                    <a href="{{ route('admin.settings.security.index', ['tab' => 'ip-rules']) }}" class="mayhen_admin_btn mayhen_admin_btn_outline" style="height: 38px;">
                        Xóa lọc
                    </a>
                @endif
            </form>
        </div>

        {{-- Bảng danh sách IP --}}
        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th style="width: 170px;">Địa Chỉ IP</th>
                        <th style="width: 130px;">Phân Loại</th>
                        <th>Lý Do Thiết Lập</th>
                        <th style="width: 160px;">Thời Hạn Chặn</th>
                        <th style="width: 110px;">Lượt Chặn</th>
                        <th style="width: 140px;">Người Tạo</th>
                        <th style="width: 110px; text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ipRules as $rule)
                        <tr>
                            <td>
                                <div style="font-family: monospace; font-size: 14px; font-weight: 600; color: #1F1D1A;">
                                    {{ $rule->ip_address }}
                                </div>
                            </td>
                            <td>
                                @if($rule->type === 'blacklist')
                                    <span class="mayhen_admin_badge mayhen_admin_badge_danger">Sổ Đen</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge_success">Sổ Trắng</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 13px; color: #4A453E;">{{ $rule->reason }}</div>
                            </td>
                            <td>
                                @if($rule->type === 'whitelist')
                                    <span style="font-size: 12px; color: #2D6A4F;">Vĩnh viễn</span>
                                @elseif($rule->blocked_until)
                                    @if($rule->isExpired())
                                        <span class="mayhen_admin_badge mayhen_admin_badge_muted">Đã hết hạn</span>
                                    @else
                                        <div style="font-size: 13px; color: #C53030; font-weight: 500;">
                                            {{ $rule->blocked_until->format('d/m/Y H:i') }}
                                        </div>
                                        <div style="font-size: 11px; color: #8A8175;">Còn {{ $rule->blocked_until->diffForHumans() }}</div>
                                    @endif
                                @else
                                    <span style="font-size: 13px; font-weight: 600; color: #C53030;">Chặn vĩnh viễn</span>
                                @endif
                            </td>
                            <td>
                                <span style="font-size: 13px; font-weight: 600; color: #1F1D1A;">{{ number_format($rule->hit_count) }}</span>
                                <span style="font-size: 11px; color: #8A8175;">lần</span>
                            </td>
                            <td>
                                <div style="font-size: 12px; color: #1F1D1A;">{{ $rule->creator?->name ?? 'Tự động' }}</div>
                                <div style="font-size: 11px; color: #8A8175;">{{ $rule->created_at->format('d/m/Y') }}</div>
                            </td>
                            <td style="text-align: right;">
                                <form action="{{ route('admin.settings.security.ip_rules.destroy', $rule->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn gỡ bỏ quy tắc IP này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="padding: 4px 8px; font-size: 12px; color: #e53e3e;" title="Gỡ bỏ quy tắc">
                                        Gỡ bỏ
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 48px; color: #8A8175;">
                                <p style="margin: 0; font-size: 14px;">Chưa có địa chỉ IP nào trong sổ theo dõi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ipRules->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #EFE8DD;">
                {{ $ipRules->links() }}
            </div>
        @endif
    </div>
@endif

{{-- TAB 2: THIẾT LẬP NGƯỠNG RATE LIMITING --}}
@if($currentTab === 'rate-limits')
    <div class="mayhen_admin_card" style="max-width: 800px;">
        <div style="padding: 20px 24px; border-bottom: 1px solid #EFE8DD;">
            <h3 style="margin: 0 0 6px 0; font-size: 16px; color: #1F1D1A; font-weight: 600;">
                Cấu Hình Ngưỡng Tần Suất Yêu Cầu (Rate Limiting Rules)
            </h3>
            <p style="margin: 0; font-size: 13px; color: #8A8175;">
                Hệ thống tự động kích hoạt HTTP 429 Too Many Requests khi một địa chỉ IP vượt quá số lượt cho phép trong 1 phút.
            </p>
        </div>

        <form action="{{ route('admin.settings.security.rate_limits.update') }}" method="POST" style="padding: 24px;">
            @csrf
            @method('PUT')

            <div style="display: flex; flex-direction: column; gap: 20px;">
                {{-- Auth Rate Limit --}}
                <div style="display: grid; grid-template-columns: 1fr 160px; gap: 20px; align-items: center; padding-bottom: 16px; border-bottom: 1px dashed #EFE8DD;">
                    <div>
                        <div style="font-weight: 600; font-size: 14px; color: #1F1D1A;">Đăng Nhập & Xác Thực (Authentication)</div>
                        <div style="font-size: 12px; color: #8A8175; margin-top: 2px;">
                            Số lần thử đăng nhập tối đa từ 1 IP trong 1 phút trước khi tạm khóa luồng xác thực (chống Brute Force).
                        </div>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <input type="number" name="auth_rate_limit_per_minute" value="{{ $rateLimitConfigs['auth_rate_limit_per_minute'] }}" min="3" max="60" class="mayhen_admin_form_input" required>
                            <span style="font-size: 12px; color: #8A8175; white-space: nowrap;">lần/m</span>
                        </div>
                    </div>
                </div>

                {{-- RSVP Rate Limit --}}
                <div style="display: grid; grid-template-columns: 1fr 160px; gap: 20px; align-items: center; padding-bottom: 16px; border-bottom: 1px dashed #EFE8DD;">
                    <div>
                        <div style="font-weight: 600; font-size: 14px; color: #1F1D1A;">Xác Nhận Tham Dự Thiệp Cưới (RSVP)</div>
                        <div style="font-size: 12px; color: #8A8175; margin-top: 2px;">
                            Giới hạn số lượt gửi biểu mẫu RSVP từ 1 địa chỉ IP trong 1 phút để chống spam dữ liệu tiệc cưới.
                        </div>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <input type="number" name="rsvp_rate_limit_per_minute" value="{{ $rateLimitConfigs['rsvp_rate_limit_per_minute'] }}" min="5" max="120" class="mayhen_admin_form_input" required>
                            <span style="font-size: 12px; color: #8A8175; white-space: nowrap;">lần/m</span>
                        </div>
                    </div>
                </div>

                {{-- Wishes Rate Limit --}}
                <div style="display: grid; grid-template-columns: 1fr 160px; gap: 20px; align-items: center; padding-bottom: 16px; border-bottom: 1px dashed #EFE8DD;">
                    <div>
                        <div style="font-weight: 600; font-size: 14px; color: #1F1D1A;">Gửi Lời Chúc & Sổ Ký Tên (Guestbook)</div>
                        <div style="font-size: 12px; color: #8A8175; margin-top: 2px;">
                            Số lời chúc tối đa được phép gửi từ 1 địa chỉ IP trong 1 phút để phòng vệ spam bot.
                        </div>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <input type="number" name="wishes_rate_limit_per_minute" value="{{ $rateLimitConfigs['wishes_rate_limit_per_minute'] }}" min="5" max="100" class="mayhen_admin_form_input" required>
                            <span style="font-size: 12px; color: #8A8175; white-space: nowrap;">lần/m</span>
                        </div>
                    </div>
                </div>

                {{-- API Rate Limit --}}
                <div style="display: grid; grid-template-columns: 1fr 160px; gap: 20px; align-items: center; padding-bottom: 16px; border-bottom: 1px dashed #EFE8DD;">
                    <div>
                        <div style="font-weight: 600; font-size: 14px; color: #1F1D1A;">Public API & Cổng Đối Tác (Developer API)</div>
                        <div style="font-size: 12px; color: #8A8175; margin-top: 2px;">
                            Tần suất gọi API công khai tối đa từ 1 IP hoặc token chưa xác định.
                        </div>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <input type="number" name="api_rate_limit_per_minute" value="{{ $rateLimitConfigs['api_rate_limit_per_minute'] }}" min="10" max="1000" class="mayhen_admin_form_input" required>
                            <span style="font-size: 12px; color: #8A8175; white-space: nowrap;">lần/m</span>
                        </div>
                    </div>
                </div>

                {{-- Strict Mode Under Attack --}}
                <div style="padding: 16px; background: #fffdfa; border: 1px solid #d4a373; border-radius: 8px;">
                    <label style="display: flex; gap: 12px; align-items: flex-start; cursor: pointer;">
                        <input type="checkbox" name="strict_rate_limiting" value="1" {{ $rateLimitConfigs['strict_rate_limiting'] ? 'checked' : '' }} style="margin-top: 3px;">
                        <div>
                            <strong style="color: #6b4d1b; font-size: 14px;">Kích Hoạt Chế Độ Phòng Vệ Cao (Under Attack Mode)</strong>
                            <div style="font-size: 12px; color: #8c6a38; margin-top: 2px; line-height: 1.4;">
                                Tự động siết chặt 50% tất cả các ngưỡng giới hạn tần suất trên và tự động đưa IP vượt ngưỡng liên tiếp vào Sổ đen tạm thời (1 giờ). Chỉ bật khi phát hiện dấu hiệu tấn công DDoS hoặc bot flood.
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">
                    Lưu Thay Đổi Cấu Hình Ngưỡng
                </button>
            </div>
        </form>
    </div>
@endif

{{-- TAB 3: NHẬT KÝ ĐE DỌA & TẤN CÔNG --}}
@if($currentTab === 'threat-logs')
    <div class="mayhen_admin_card">
        {{-- Bộ lọc Threat Logs --}}
        <div style="padding: 16px 20px; border-bottom: 1px solid #EFE8DD;">
            <form action="{{ route('admin.settings.security.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="tab" value="threat-logs">
                
                <div style="flex: 1; min-width: 240px;">
                    <input type="text" name="ip" value="{{ request('ip') }}" placeholder="Lọc theo địa chỉ IP..." 
                           class="mayhen_admin_form_input" style="height: 38px;">
                </div>

                <div style="width: 220px;">
                    <select name="event_type" class="mayhen_admin_form_select" style="height: 38px;">
                        <option value="">Tất cả loại đe dọa</option>
                        <option value="rate_limit_exceeded" {{ request('event_type') === 'rate_limit_exceeded' ? 'selected' : '' }}>Vượt ngưỡng Rate Limit</option>
                        <option value="failed_login_burst" {{ request('event_type') === 'failed_login_burst' ? 'selected' : '' }}>Dò mật khẩu Brute Force</option>
                        <option value="bot_spam" {{ request('event_type') === 'bot_spam' ? 'selected' : '' }}>Hành vi Spam Bot</option>
                        <option value="sql_injection_attempt" {{ request('event_type') === 'sql_injection_attempt' ? 'selected' : '' }}>Mã độc SQL Injection</option>
                    </select>
                </div>

                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_secondary" style="height: 38px;">
                    Lọc Nhật Ký
                </button>

                @if(request()->anyFilled(['ip', 'event_type']))
                    <a href="{{ route('admin.settings.security.index', ['tab' => 'threat-logs']) }}" class="mayhen_admin_btn mayhen_admin_btn_outline" style="height: 38px;">
                        Xóa lọc
                    </a>
                @endif
            </form>
        </div>

        {{-- Bảng Threat Logs --}}
        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th style="width: 170px;">Thời Gian</th>
                        <th style="width: 220px;">Loại Đe Dọa</th>
                        <th style="width: 150px;">Địa Chỉ IP</th>
                        <th style="width: 220px;">Endpoint Mục Tiêu</th>
                        <th>User Agent / Trình Duyệt</th>
                        <th style="width: 130px; text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($threatLogs as $log)
                        <tr>
                            <td>
                                <div style="font-size: 13px; font-weight: 500; color: #1F1D1A;">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                                <div style="font-size: 11px; color: #8A8175;">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <span class="mayhen_admin_badge mayhen_admin_badge_{{ $log->event_badge_color }}" style="font-size: 11px;">
                                    {{ $log->event_label }}
                                </span>
                            </td>
                            <td>
                                <div style="font-family: monospace; font-size: 13px; font-weight: 600; color: #1F1D1A;">{{ $log->ip_address }}</div>
                            </td>
                            <td>
                                <div style="font-family: monospace; font-size: 12px; color: #2B6CB0; word-break: break-all;">{{ $log->endpoint }}</div>
                            </td>
                            <td>
                                <div style="font-size: 11px; color: #718096; max-width: 350px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $log->user_agent }}">
                                    {{ $log->user_agent ?: 'Unknown User Agent' }}
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <span class="mayhen_admin_badge mayhen_admin_badge_warning" style="font-size: 11px;">
                                    {{ $log->action_taken }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 48px; color: #8A8175;">
                                <p style="margin: 0; font-size: 14px;">Chưa có sự cố an ninh hoặc đe dọa nào được ghi nhận.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($threatLogs->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #EFE8DD;">
                {{ $threatLogs->links() }}
            </div>
        @endif
    </div>
@endif

{{-- MODAL THÊM QUY TẮC IP MỚI --}}
<div id="addIpRuleModal" class="mayhen_admin_modal" style="display: none; position: fixed; inset: 0; background: rgba(31, 29, 26, 0.45); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="mayhen_admin_modal_dialog" style="background: #ffffff; border-radius: 12px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
        <form action="{{ route('admin.settings.security.ip_rules.store') }}" method="POST">
            @csrf
            <div style="padding: 20px 24px; border-bottom: 1px solid #EFE8DD; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 18px; color: #1F1D1A; font-weight: 600;">Thêm Quy Tắc IP Tường Lửa</h3>
                <button type="button" onclick="closeModal('addIpRuleModal')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #8A8175;">&times;</button>
            </div>

            <div style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                        Địa Chỉ IP (IPv4 hoặc IPv6) <span style="color: #e53e3e;">*</span>
                    </label>
                    <input type="text" name="ip_address" required placeholder="Ví dụ: 14.232.208.55 hoặc 2001:db8::1" class="mayhen_admin_form_input" style="font-family: monospace;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                            Phân Loại Quy Tắc <span style="color: #e53e3e;">*</span>
                        </label>
                        <select name="type" class="mayhen_admin_form_select" required>
                            <option value="blacklist">Sổ đen (Blacklist - Chặn)</option>
                            <option value="whitelist">Sổ trắng (Whitelist - Tin cậy)</option>
                        </select>
                    </div>

                    <div>
                        <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                            Thời Hạn Chặn
                        </label>
                        <select name="duration_hours" class="mayhen_admin_form_select">
                            <option value="">Vĩnh viễn (Không thời hạn)</option>
                            <option value="1">1 giờ (Tạm khóa)</option>
                            <option value="24">24 giờ (1 ngày)</option>
                            <option value="168">7 ngày (1 tuần)</option>
                            <option value="720">30 ngày (1 tháng)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                        Lý Do Thiết Lập <span style="color: #e53e3e;">*</span>
                    </label>
                    <input type="text" name="reason" required placeholder="Ví dụ: Spam bình luận liên tục, Dò mật khẩu, IP máy chủ Studio..." class="mayhen_admin_form_input">
                </div>
            </div>

            <div style="padding: 16px 24px; border-top: 1px solid #EFE8DD; display: flex; justify-content: flex-end; gap: 12px; background: #FAF8F5; border-radius: 0 0 12px 12px;">
                <button type="button" onclick="closeModal('addIpRuleModal')" class="mayhen_admin_btn mayhen_admin_btn_outline">Hủy Bỏ</button>
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">Áp Dụng Quy Tắc</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.style.display = 'flex';
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.style.display = 'none';
}

window.addEventListener('click', function(e) {
    const modal = document.getElementById('addIpRuleModal');
    if (modal && e.target === modal) modal.style.display = 'none';
});
</script>
@endsection

