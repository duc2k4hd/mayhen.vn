@extends('admin.layouts.master')

@section('title', 'Quản Trị Khóa API & Webhook Ngoại Vi')

@section('content')
<div class="mayhen_admin_page_header">
    <div class="mayhen_admin_page_header_left">
        <h1 class="mayhen_admin_page_title">Quản Trị Khóa API & Webhooks Ngoại Vi</h1>
        <p class="mayhen_admin_page_desc">
            Cổng quản trị tích hợp mở dành cho đối tác Studio, nhà phát triển và hệ sinh thái kết nối ngoại vi theo chuẩn Mục 179 PRD V3.
        </p>
    </div>
    <div class="mayhen_admin_page_header_actions">
        @if($currentTab === 'api-keys')
            <button type="button" class="mayhen_admin_btn mayhen_admin_btn_primary" onclick="openModal('createApiKeyModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tạo Khóa API Mới
            </button>
        @elseif($currentTab === 'webhooks')
            <button type="button" class="mayhen_admin_btn mayhen_admin_btn_primary" onclick="openModal('createWebhookModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Đăng Ký Webhook Nền Tảng
            </button>
        @endif
    </div>
</div>

{{-- Hộp Cảnh Báo Hiển Thị Token Duy Nhất 1 Lần --}}
@if(session('new_plain_key'))
    <div class="mayhen_admin_card" style="margin-bottom: 24px; border: 1px solid #d4a373; background: linear-gradient(135deg, #fffdfa 0%, #fef8ee 100%);">
        <div style="display: flex; gap: 16px; align-items: flex-start; padding: 20px;">
            <div style="width: 40px; height: 40px; border-radius: 8px; background: #faedcd; color: #b07d3b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
                </svg>
            </div>
            <div style="flex: 1;">
                <h3 style="margin: 0 0 6px 0; font-size: 16px; color: #6b4d1b; font-weight: 600;">
                    Khóa Bí Mật Mới: "{{ session('new_key_name') }}"
                </h3>
                <p style="margin: 0 0 12px 0; font-size: 13px; color: #8c6a38; line-height: 1.5;">
                    ⚠️ <strong>Lưu ý an ninh tối cao:</strong> Chuỗi mã khóa bên dưới chỉ được hiển thị <strong>DUY NHẤT 1 LẦN NÀY</strong>. Hãy sao chép và lưu trữ an toàn vào hệ thống máy chủ của bạn. Hệ thống Mây Hẹn sẽ chỉ lưu trữ bản mã băm SHA-256 và không bao giờ hiển thị lại chuỗi ký tự này.
                </p>
                <div style="display: flex; gap: 8px; align-items: center; max-width: 650px;">
                    <input type="text" id="newPlainKeyInput" readonly value="{{ session('new_plain_key') }}" 
                           style="flex: 1; font-family: monospace; font-size: 14px; padding: 10px 14px; background: #ffffff; border: 1px solid #d4a373; border-radius: 6px; color: #1F1D1A; font-weight: 600;">
                    <button type="button" class="mayhen_admin_btn mayhen_admin_btn_primary" onclick="copyPlainKey()" id="copyKeyBtn" style="white-space: nowrap;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        Sao Chép Khóa
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- 4 Thẻ KPI Chỉ Số Tích Hợp --}}
<div class="mayhen_admin_kpi_grid" style="margin-bottom: 24px;">
    <div class="mayhen_admin_kpi_card">
        <div class="mayhen_admin_kpi_icon" style="background: rgba(197, 168, 128, 0.15); color: #9C7A4A;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
            </svg>
        </div>
        <div class="mayhen_admin_kpi_content">
            <div class="mayhen_admin_kpi_label">Khóa API Phát Hành</div>
            <div class="mayhen_admin_kpi_value">{{ number_format($kpis['total_api_keys']) }}</div>
            <div class="mayhen_admin_kpi_sub" style="color: #2D6A4F;">{{ $kpis['active_api_keys'] }} khóa đang hoạt động</div>
        </div>
    </div>

    <div class="mayhen_admin_kpi_card">
        <div class="mayhen_admin_kpi_icon" style="background: rgba(74, 144, 226, 0.12); color: #2B6CB0;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="16 18 22 12 16 6"></polyline>
                <polyline points="8 6 2 12 8 18"></polyline>
            </svg>
        </div>
        <div class="mayhen_admin_kpi_content">
            <div class="mayhen_admin_kpi_label">Tổng Webhooks Đăng Ký</div>
            <div class="mayhen_admin_kpi_value">{{ number_format($kpis['total_webhooks']) }}</div>
            <div class="mayhen_admin_kpi_sub">{{ $kpis['platform_webhooks'] }} webhook nền tảng</div>
        </div>
    </div>

    <div class="mayhen_admin_kpi_card">
        <div class="mayhen_admin_kpi_icon" style="background: rgba(45, 106, 79, 0.12); color: #2D6A4F;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <div class="mayhen_admin_kpi_content">
            <div class="mayhen_admin_kpi_label">Tỷ Lệ Giao Vận (24h)</div>
            <div class="mayhen_admin_kpi_value">{{ $kpis['success_rate_24h'] }}%</div>
            <div class="mayhen_admin_kpi_sub">{{ number_format($kpis['total_deliveries_24h']) }} lượt phát trong ngày</div>
        </div>
    </div>

    <div class="mayhen_admin_kpi_card">
        <div class="mayhen_admin_kpi_icon" style="background: {{ $kpis['circuit_broken_webhooks'] > 0 ? 'rgba(217, 83, 79, 0.12)' : 'rgba(108, 117, 125, 0.12)' }}; color: {{ $kpis['circuit_broken_webhooks'] > 0 ? '#C53030' : '#6C757D' }};">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
            </svg>
        </div>
        <div class="mayhen_admin_kpi_content">
            <div class="mayhen_admin_kpi_label">Sự Cố Ngắt Mạch</div>
            <div class="mayhen_admin_kpi_value" style="color: {{ $kpis['circuit_broken_webhooks'] > 0 ? '#C53030' : 'inherit' }};">
                {{ number_format($kpis['circuit_broken_webhooks']) }}
            </div>
            <div class="mayhen_admin_kpi_sub">Webhook lỗi liên tiếp ≥ 10 lần</div>
        </div>
    </div>
</div>

{{-- Navigation Tabs --}}
<div class="mayhen_admin_tabs" style="display: flex; gap: 8px; border-bottom: 1px solid #EFE8DD; margin-bottom: 24px;">
    <a href="{{ route('admin.settings.developer.index', ['tab' => 'api-keys']) }}" 
       class="mayhen_admin_tab {{ $currentTab === 'api-keys' ? 'active' : '' }}"
       style="padding: 10px 18px; font-size: 14px; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'api-keys' ? '#C5A880' : 'transparent' }}; color: {{ $currentTab === 'api-keys' ? '#1F1D1A' : '#6c757d' }};">
        🔑 Khóa API Nhà Phát Triển ({{ number_format($kpis['total_api_keys']) }})
    </a>
    <a href="{{ route('admin.settings.developer.index', ['tab' => 'webhooks']) }}" 
       class="mayhen_admin_tab {{ $currentTab === 'webhooks' ? 'active' : '' }}"
       style="padding: 10px 18px; font-size: 14px; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'webhooks' ? '#C5A880' : 'transparent' }}; color: {{ $currentTab === 'webhooks' ? '#1F1D1A' : '#6c757d' }};">
        📡 Quản Trị Webhooks ({{ number_format($kpis['total_webhooks']) }})
    </a>
    <a href="{{ route('admin.settings.developer.index', ['tab' => 'deliveries']) }}" 
       class="mayhen_admin_tab {{ $currentTab === 'deliveries' ? 'active' : '' }}"
       style="padding: 10px 18px; font-size: 14px; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'deliveries' ? '#C5A880' : 'transparent' }}; color: {{ $currentTab === 'deliveries' ? '#1F1D1A' : '#6c757d' }};">
        📜 Nhật Ký Giao Vận (Deliveries Log)
    </a>
</div>

{{-- TAB 1: KHÓA API --}}
@if($currentTab === 'api-keys')
    <div class="mayhen_admin_card">
        {{-- Bộ lọc API Keys --}}
        <div style="padding: 16px 20px; border-bottom: 1px solid #EFE8DD;">
            <form action="{{ route('admin.settings.developer.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="tab" value="api-keys">
                
                <div style="flex: 1; min-width: 240px;">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên ứng dụng, đối tác hoặc tiền tố khóa..." 
                           class="mayhen_admin_form_input" style="height: 38px;">
                </div>

                <div style="width: 160px;">
                    <select name="environment" class="mayhen_admin_form_select" style="height: 38px;">
                        <option value="">Tất cả môi trường</option>
                        <option value="live" {{ request('environment') === 'live' ? 'selected' : '' }}>Chính thức (Live)</option>
                        <option value="test" {{ request('environment') === 'test' ? 'selected' : '' }}>Thử nghiệm (Test)</option>
                    </select>
                </div>

                <div style="width: 160px;">
                    <select name="status" class="mayhen_admin_form_select" style="height: 38px;">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Đã thu hồi</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
                    </select>
                </div>

                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_secondary" style="height: 38px;">
                    Lọc Khóa
                </button>

                @if(request()->anyFilled(['search', 'environment', 'status']))
                    <a href="{{ route('admin.settings.developer.index', ['tab' => 'api-keys']) }}" class="mayhen_admin_btn mayhen_admin_btn_outline" style="height: 38px;">
                        Xóa lọc
                    </a>
                @endif
            </form>
        </div>

        {{-- Bảng danh sách API Keys --}}
        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th style="width: 220px;">Tên Ứng Dụng / Đối Tác</th>
                        <th style="width: 110px;">Môi Trường</th>
                        <th style="width: 250px;">Khóa Bí Mật (Masked)</th>
                        <th>Phạm Vi Quyền (Scopes)</th>
                        <th style="width: 110px;">Giới Hạn</th>
                        <th style="width: 150px;">Lần Dùng Cuối</th>
                        <th style="width: 130px;">Trạng Thái</th>
                        <th style="width: 120px; text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($apiKeys as $key)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #1F1D1A; font-size: 14px;">{{ $key->name }}</div>
                                <div style="font-size: 12px; color: #8A8175;">Tạo bởi: {{ $key->creator?->name ?? 'Hệ thống' }}</div>
                            </td>
                            <td>
                                @if($key->environment === 'live')
                                    <span class="mayhen_admin_badge mayhen_admin_badge_success" style="font-size: 11px; text-transform: uppercase;">Live</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge_warning" style="font-size: 11px; text-transform: uppercase;">Test</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-family: monospace; font-size: 13px; color: #4A453E; background: #FAF8F5; padding: 4px 8px; border-radius: 4px; display: inline-block;">
                                    {{ $key->masked_key }}
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                    @foreach($key->scopes as $scope)
                                        <span class="mayhen_admin_badge mayhen_admin_badge_info" style="font-size: 11px; background: #f0f4f8; color: #2c5282;">
                                            {{ $scope }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 13px; font-weight: 500;">{{ $key->rate_limit_per_minute }}</span>
                                <span style="font-size: 11px; color: #8A8175;">req/m</span>
                            </td>
                            <td>
                                @if($key->last_used_at)
                                    <div style="font-size: 13px; color: #1F1D1A;">{{ $key->last_used_at->format('d/m/Y H:i') }}</div>
                                    <div style="font-size: 11px; color: #8A8175;">IP: {{ $key->last_used_ip ?? 'N/A' }}</div>
                                @else
                                    <span style="font-size: 12px; color: #a0aec0; font-style: italic;">Chưa dùng</span>
                                @endif
                            </td>
                            <td>
                                @if($key->status === 'revoked')
                                    <span class="mayhen_admin_badge mayhen_admin_badge_danger">Đã thu hồi</span>
                                @elseif($key->isExpired())
                                    <span class="mayhen_admin_badge mayhen_admin_badge_warning">Hết hạn</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge_success">Hoạt động</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    @if($key->status === 'active' && ! $key->isExpired())
                                        <form action="{{ route('admin.settings.developer.api_keys.revoke', $key->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn thu hồi Khóa API này? Ứng dụng tích hợp sẽ lập tức mất quyền truy cập.');">
                                            @csrf
                                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="padding: 4px 8px; font-size: 12px; color: #e53e3e;" title="Thu hồi khóa">
                                                Thu hồi
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.settings.developer.api_keys.destroy', $key->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn khóa này khỏi cơ sở dữ liệu?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="padding: 4px 8px; font-size: 12px; color: #718096;" title="Xóa khóa">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 48px; color: #8A8175;">
                                <p style="margin: 0; font-size: 14px;">Chưa có Khóa API nào được phát hành theo bộ lọc hiện tại.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($apiKeys->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #EFE8DD;">
                {{ $apiKeys->links() }}
            </div>
        @endif
    </div>
@endif

{{-- TAB 2: WEBHOOKS --}}
@if($currentTab === 'webhooks')
    <div class="mayhen_admin_card">
        {{-- Bộ lọc Webhooks --}}
        <div style="padding: 16px 20px; border-bottom: 1px solid #EFE8DD;">
            <form action="{{ route('admin.settings.developer.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="tab" value="webhooks">
                
                <div style="flex: 1; min-width: 240px;">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên webhook hoặc endpoint URL..." 
                           class="mayhen_admin_form_input" style="height: 38px;">
                </div>

                <div style="width: 180px;">
                    <select name="scope" class="mayhen_admin_form_select" style="height: 38px;">
                        <option value="">Tất cả phạm vi</option>
                        <option value="platform" {{ request('scope') === 'platform' ? 'selected' : '' }}>Toàn nền tảng (Platform)</option>
                        <option value="wedding" {{ request('scope') === 'wedding' ? 'selected' : '' }}>Tiệc cưới (Wedding Level)</option>
                    </select>
                </div>

                <div style="width: 180px;">
                    <select name="status" class="mayhen_admin_form_select" style="height: 38px;">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Đang tạm ngắt</option>
                        <option value="broken" {{ request('status') === 'broken' ? 'selected' : '' }}>Ngắt mạch (Circuit Broken)</option>
                    </select>
                </div>

                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_secondary" style="height: 38px;">
                    Lọc Webhooks
                </button>

                @if(request()->anyFilled(['search', 'scope', 'status']))
                    <a href="{{ route('admin.settings.developer.index', ['tab' => 'webhooks']) }}" class="mayhen_admin_btn mayhen_admin_btn_outline" style="height: 38px;">
                        Xóa lọc
                    </a>
                @endif
            </form>
        </div>

        {{-- Bảng Webhooks --}}
        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th style="width: 220px;">Tên Webhook & Cấp Độ</th>
                        <th>Payload URL (Endpoint)</th>
                        <th style="width: 220px;">Sự Kiện Lắng Nghe</th>
                        <th style="width: 140px;">Trạng Thái</th>
                        <th style="width: 160px;">Lần Gửi Cuối</th>
                        <th style="width: 160px; text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($webhooks as $wh)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #1F1D1A; font-size: 14px;">{{ $wh->name }}</div>
                                <div style="font-size: 12px; color: #8A8175;">
                                    @if($wh->isPlatformLevel())
                                        <span style="color: #9C7A4A; font-weight: 500;">🌐 Cấp Nền Tảng (Platform)</span>
                                    @else
                                        <span>💒 {{ $wh->target_label }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div style="font-family: monospace; font-size: 13px; color: #2B6CB0; word-break: break-all;">
                                    {{ $wh->payload_url }}
                                </div>
                                <div style="font-size: 11px; color: #8A8175; margin-top: 2px;">
                                    Khóa ký HMAC: <span style="font-family: monospace;">{{ Str::limit($wh->secret, 16) }}</span>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                    @foreach($wh->events as $evt)
                                        <span class="mayhen_admin_badge mayhen_admin_badge_info" style="font-size: 11px;">{{ $evt }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if($wh->isCircuitBroken())
                                    <span class="mayhen_admin_badge mayhen_admin_badge_danger">Ngắt mạch ({{ $wh->failure_count }} lỗi)</span>
                                @elseif($wh->is_active)
                                    <span class="mayhen_admin_badge mayhen_admin_badge_success">Hoạt động</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge_muted">Tạm ngắt</span>
                                @endif
                            </td>
                            <td>
                                @if($wh->last_dispatched_at)
                                    <div style="font-size: 13px; color: #1F1D1A;">{{ $wh->last_dispatched_at->format('d/m/Y H:i') }}</div>
                                    <div style="font-size: 11px; color: {{ $wh->last_status_code >= 200 && $wh->last_status_code < 300 ? '#2D6A4F' : '#C53030' }}; font-weight: 600;">
                                        Mã HTTP: {{ $wh->last_status_code ?? 'N/A' }}
                                    </div>
                                @else
                                    <span style="font-size: 12px; color: #a0aec0; font-style: italic;">Chưa phát</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    {{-- Nút Ping Test --}}
                                    <form action="{{ route('admin.settings.developer.webhooks.ping', $wh->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="padding: 4px 8px; font-size: 12px;" title="Gửi gói tin Ping Test">
                                            Ping
                                        </button>
                                    </form>

                                    {{-- Nút Toggle Active --}}
                                    <form action="{{ route('admin.settings.developer.webhooks.toggle', $wh->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="padding: 4px 8px; font-size: 12px;" title="{{ $wh->is_active ? 'Tạm ngắt webhook' : 'Kích hoạt webhook' }}">
                                            {{ $wh->is_active ? 'Tạm ngắt' : 'Bật lại' }}
                                        </button>
                                    </form>

                                    {{-- Nút Xóa --}}
                                    <form action="{{ route('admin.settings.developer.webhooks.destroy', $wh->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa Webhook này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="padding: 4px 8px; font-size: 12px; color: #e53e3e;" title="Xóa webhook">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 48px; color: #8A8175;">
                                <p style="margin: 0; font-size: 14px;">Chưa có Webhook nào được thiết lập.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($webhooks->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #EFE8DD;">
                {{ $webhooks->links() }}
            </div>
        @endif
    </div>
@endif

{{-- TAB 3: DELIVERIES LOG --}}
@if($currentTab === 'deliveries')
    <div class="mayhen_admin_card">
        {{-- Bộ lọc Deliveries --}}
        <div style="padding: 16px 20px; border-bottom: 1px solid #EFE8DD;">
            <form action="{{ route('admin.settings.developer.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="tab" value="deliveries">
                
                <div style="width: 220px;">
                    <select name="status" class="mayhen_admin_form_select" style="height: 38px;">
                        <option value="">Tất cả trạng thái giao vận</option>
                        <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Thành công (2xx)</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Thất bại / Lỗi kết nối</option>
                    </select>
                </div>

                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_secondary" style="height: 38px;">
                    Lọc Nhật Ký
                </button>

                @if(request()->anyFilled(['status', 'webhook_id']))
                    <a href="{{ route('admin.settings.developer.index', ['tab' => 'deliveries']) }}" class="mayhen_admin_btn mayhen_admin_btn_outline" style="height: 38px;">
                        Xóa lọc
                    </a>
                @endif
            </form>
        </div>

        {{-- Bảng Deliveries --}}
        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th style="width: 170px;">Thời Gian</th>
                        <th style="width: 220px;">Webhook</th>
                        <th style="width: 160px;">Sự Kiện</th>
                        <th style="width: 120px;">Mã HTTP</th>
                        <th style="width: 120px;">Độ Trễ</th>
                        <th>Chi Tiết / Phản Hồi</th>
                        <th style="width: 100px; text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $del)
                        <tr>
                            <td>
                                <div style="font-size: 13px; font-weight: 500; color: #1F1D1A;">{{ $del->created_at?->format('d/m/Y H:i:s') }}</div>
                                <div style="font-size: 11px; color: #8A8175;">{{ $del->created_at?->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #1F1D1A; font-size: 13px;">{{ $del->webhook?->name ?? 'Webhook đã xóa' }}</div>
                                <div style="font-size: 11px; color: #8A8175; font-family: monospace;">{{ Str::limit($del->webhook?->payload_url, 35) }}</div>
                            </td>
                            <td>
                                <span class="mayhen_admin_badge mayhen_admin_badge_info" style="font-size: 11px;">{{ $del->event_type }}</span>
                            </td>
                            <td>
                                @if($del->is_success)
                                    <span class="mayhen_admin_badge mayhen_admin_badge_success">HTTP {{ $del->response_status }}</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge_danger">HTTP {{ $del->response_status ?? 0 }}</span>
                                @endif
                            </td>
                            <td>
                                <span style="font-size: 13px; font-weight: 500;">{{ $del->duration_ms }} ms</span>
                            </td>
                            <td>
                                @if($del->is_success)
                                    <div style="font-size: 12px; color: #2D6A4F; font-family: monospace; max-width: 400px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $del->response_body ?: 'Thành công (OK)' }}
                                    </div>
                                @else
                                    <div style="font-size: 12px; color: #C53030; font-weight: 500;">
                                        {{ $del->error_message ?: 'Lỗi không xác định' }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if($del->webhook)
                                    <form action="{{ route('admin.settings.developer.deliveries.retry', $del->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="padding: 4px 8px; font-size: 12px;" title="Gửi lại lượt phát này">
                                            Gửi lại
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 48px; color: #8A8175;">
                                <p style="margin: 0; font-size: 14px;">Chưa có lượt giao vận webhook nào được ghi nhận.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($deliveries->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #EFE8DD;">
                {{ $deliveries->links() }}
            </div>
        @endif
    </div>
@endif

{{-- MODAL 1: TẠO KHÓA API MỚI --}}
<div id="createApiKeyModal" class="mayhen_admin_modal" style="display: none; position: fixed; inset: 0; background: rgba(31, 29, 26, 0.45); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="mayhen_admin_modal_dialog" style="background: #ffffff; border-radius: 12px; max-width: 580px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
        <form action="{{ route('admin.settings.developer.api_keys.store') }}" method="POST">
            @csrf
            <div style="padding: 20px 24px; border-bottom: 1px solid #EFE8DD; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 18px; color: #1F1D1A; font-weight: 600;">Phát Hành Khóa API Mới</h3>
                <button type="button" onclick="closeModal('createApiKeyModal')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #8A8175;">&times;</button>
            </div>

            <div style="padding: 24px; display: flex; flex-direction: column; gap: 18px;">
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                        Tên Ứng Dụng / Đối Tác Tích Hợp <span style="color: #e53e3e;">*</span>
                    </label>
                    <input type="text" name="name" required placeholder="Ví dụ: MarryHub CRM SDK, Studio Đà Nẵng Connector..." class="mayhen_admin_form_input">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                            Môi Trường Hoạt Động <span style="color: #e53e3e;">*</span>
                        </label>
                        <select name="environment" class="mayhen_admin_form_select" required>
                            <option value="live">Chính thức (Live - mh_live_)</option>
                            <option value="test">Thử nghiệm (Test - mh_test_)</option>
                        </select>
                    </div>

                    <div>
                        <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                            Giới Hạn Tốc Độ (req/phút)
                        </label>
                        <input type="number" name="rate_limit_per_minute" value="60" min="10" max="1000" class="mayhen_admin_form_input">
                    </div>
                </div>

                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                        Ngày Hết Hạn (Để trống = Vĩnh viễn)
                    </label>
                    <input type="date" name="expires_at" class="mayhen_admin_form_input">
                </div>

                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">
                        Phân Quyền Truy Cập (Scopes) <span style="color: #e53e3e;">*</span>
                    </label>
                    <div style="display: flex; flex-direction: column; gap: 8px; max-height: 200px; overflow-y: auto; padding: 12px; background: #FAF8F5; border-radius: 6px; border: 1px solid #EFE8DD;">
                        @foreach($availableScopes as $scopeKey => $scopeMeta)
                            <label style="display: flex; gap: 10px; align-items: flex-start; cursor: pointer; font-size: 13px;">
                                <input type="checkbox" name="scopes[]" value="{{ $scopeKey }}" {{ $scopeKey === 'templates:read' ? 'checked' : '' }} style="margin-top: 2px;">
                                <div>
                                    <strong style="color: #1F1D1A;">{{ $scopeMeta['name'] }}</strong>
                                    <span style="font-family: monospace; font-size: 11px; color: #8A8175;">({{ $scopeKey }})</span>
                                    <div style="font-size: 11px; color: #8A8175; margin-top: 1px;">{{ $scopeMeta['description'] }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div style="padding: 16px 24px; border-top: 1px solid #EFE8DD; display: flex; justify-content: flex-end; gap: 12px; background: #FAF8F5; border-radius: 0 0 12px 12px;">
                <button type="button" onclick="closeModal('createApiKeyModal')" class="mayhen_admin_btn mayhen_admin_btn_outline">Hủy Bỏ</button>
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">Phát Hành Khóa API</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: ĐĂNG KÝ WEBHOOK NỀN TẢNG MỚI --}}
<div id="createWebhookModal" class="mayhen_admin_modal" style="display: none; position: fixed; inset: 0; background: rgba(31, 29, 26, 0.45); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="mayhen_admin_modal_dialog" style="background: #ffffff; border-radius: 12px; max-width: 580px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
        <form action="{{ route('admin.settings.developer.webhooks.store') }}" method="POST">
            @csrf
            <div style="padding: 20px 24px; border-bottom: 1px solid #EFE8DD; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 18px; color: #1F1D1A; font-weight: 600;">Đăng Ký Webhook Nền Tảng</h3>
                <button type="button" onclick="closeModal('createWebhookModal')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #8A8175;">&times;</button>
            </div>

            <div style="padding: 24px; display: flex; flex-direction: column; gap: 18px;">
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                        Tên Định Danh Webhook <span style="color: #e53e3e;">*</span>
                    </label>
                    <input type="text" name="name" required placeholder="Ví dụ: ERP MayHen Trung Tâm, Telegram Ops Bot..." class="mayhen_admin_form_input">
                </div>

                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                        Payload URL (Endpoint tiếp nhận POST JSON) <span style="color: #e53e3e;">*</span>
                    </label>
                    <input type="url" name="payload_url" required placeholder="https://api.yourdomain.com/webhooks/mayhen" class="mayhen_admin_form_input">
                    <p style="margin: 4px 0 0 0; font-size: 11px; color: #8A8175;">
                        Hệ thống sẽ gửi gói tin POST có kèm header <code style="font-family: monospace;">X-MayHen-Signature</code> (HMAC-SHA256).
                    </p>
                </div>

                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px;">
                        Sự Kiện Đăng Ký Lắng Nghe <span style="color: #e53e3e;">*</span>
                    </label>
                    <div style="display: flex; flex-direction: column; gap: 8px; max-height: 200px; overflow-y: auto; padding: 12px; background: #FAF8F5; border-radius: 6px; border: 1px solid #EFE8DD;">
                        @foreach($availableEvents as $eventKey => $eventMeta)
                            <label style="display: flex; gap: 10px; align-items: flex-start; cursor: pointer; font-size: 13px;">
                                <input type="checkbox" name="events[]" value="{{ $eventKey }}" {{ in_array($eventKey, ['order.paid', 'invitation.published']) ? 'checked' : '' }} style="margin-top: 2px;">
                                <div>
                                    <strong style="color: #1F1D1A;">{{ $eventMeta['name'] }}</strong>
                                    <span style="font-family: monospace; font-size: 11px; color: #8A8175;">({{ $eventKey }})</span>
                                    <div style="font-size: 11px; color: #8A8175; margin-top: 1px;">{{ $eventMeta['description'] }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div style="padding: 16px 24px; border-top: 1px solid #EFE8DD; display: flex; justify-content: flex-end; gap: 12px; background: #FAF8F5; border-radius: 0 0 12px 12px;">
                <button type="button" onclick="closeModal('createWebhookModal')" class="mayhen_admin_btn mayhen_admin_btn_outline">Hủy Bỏ</button>
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">Đăng Ký Webhook</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = 'none';
    }
}

function copyPlainKey() {
    const input = document.getElementById('newPlainKeyInput');
    const btn = document.getElementById('copyKeyBtn');
    if (input) {
        input.select();
        navigator.clipboard.writeText(input.value).then(() => {
            if (btn) {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '✓ Đã Sao Chép!';
                btn.style.background = '#2D6A4F';
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.style.background = '';
                }, 2500);
            }
        });
    }
}

// Đóng modal khi click ra ngoài vùng dialog
window.addEventListener('click', function(e) {
    ['createApiKeyModal', 'createWebhookModal'].forEach(id => {
        const modal = document.getElementById(id);
        if (modal && e.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>
@endsection

