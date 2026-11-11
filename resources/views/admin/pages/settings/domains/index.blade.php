@extends('admin.layouts.master')

@section('title', 'Quản Trị Tên Miền Riêng & Định Tuyến Liên Kết')

@section('content')
<div class="mayhen_admin_domains_cockpit">
    <!-- Header Section -->
    <div class="mayhen_admin_page_header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="mayhen_admin_badge mayhen_admin_badge--gold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">DNS & Custom Domains</span>
                <span style="color: var(--admin-text-muted); font-size: 0.85rem;">Mục 172 PRD V3 & DOM-01, DOM-02</span>
            </div>
            <h1 style="font-size: 1.6rem; font-weight: 600; color: var(--admin-charcoal); margin: 0;">
                Quản Trị Tên Miền Riêng & Định Tuyến Liên Kết Thiệp Cưới
            </h1>
            <p style="margin: 0.25rem 0 0 0; color: var(--admin-text-muted); font-size: 0.9rem;">
                Kiểm định bản ghi DNS CNAME/A, cấp phát SSL tự động và giám sát định tuyến tên miền độc bản cho cặp đôi.
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('admin.settings.platform', ['group' => 'brand_seo']) }}" class="mayhen_admin_btn mayhen_admin_btn--outline" style="font-size: 0.85rem;">
                Thiết Lập SEO
            </a>
            <a href="{{ route('admin.settings.domains.index', ['tab' => 'diagnostic']) }}" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="font-size: 0.85rem;">
                🔍 Chẩn Đoán DNS
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="mayhen_admin_kpi_grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid #7C3AED;">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--admin-text-muted); text-transform: uppercase;">Tên Miền Riêng (Custom)</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--admin-charcoal); margin: 0.4rem 0 0.25rem 0;">
                {{ number_format($kpis['total_custom']) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Định danh cá nhân độc bản (.wedding, .vn)</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid var(--admin-gold);">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--admin-text-muted); text-transform: uppercase;">Subdomain Mây Hẹn</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--admin-charcoal); margin: 0.4rem 0 0.25rem 0;">
                {{ number_format($kpis['total_subdomains']) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Vanity URLs (*.mayhen.vn)</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid #059669;">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--admin-text-muted); text-transform: uppercase;">Đang Hoạt Động (SSL Active)</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #059669; margin: 0.4rem 0 0.25rem 0;">
                {{ number_format($kpis['active_count']) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Định tuyến HTTPS an toàn tuyệt đối</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid #D97706;">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--admin-text-muted); text-transform: uppercase;">Chờ Xác Minh DNS</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #D97706; margin: 0.4rem 0 0.25rem 0;">
                {{ number_format($kpis['pending_count']) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Cần kiểm tra bản ghi CNAME host</div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div style="display: flex; gap: 0.5rem; border-bottom: 1px solid var(--admin-border-color); margin-bottom: 1.5rem;">
        <a href="{{ route('admin.settings.domains.index', ['tab' => 'domains']) }}" 
           class="mayhen_admin_tab {{ $currentTab === 'domains' ? 'mayhen_admin_tab--active' : '' }}"
           style="padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'domains' ? 'var(--admin-gold)' : 'transparent' }}; color: {{ $currentTab === 'domains' ? 'var(--admin-gold)' : 'var(--admin-text-muted)' }};">
            Danh Sách Tên Miền & Subdomain ({{ $domains->total() }})
        </a>
        <a href="{{ route('admin.settings.domains.index', ['tab' => 'settings']) }}" 
           class="mayhen_admin_tab {{ $currentTab === 'settings' ? 'mayhen_admin_tab--active' : '' }}"
           style="padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'settings' ? 'var(--admin-gold)' : 'transparent' }}; color: {{ $currentTab === 'settings' ? 'var(--admin-gold)' : 'var(--admin-text-muted)' }};">
            Thiết Lập Định Tuyến Máy Chủ
        </a>
        <a href="{{ route('admin.settings.domains.index', ['tab' => 'diagnostic']) }}" 
           class="mayhen_admin_tab {{ $currentTab === 'diagnostic' ? 'mayhen_admin_tab--active' : '' }}"
           style="padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'diagnostic' ? 'var(--admin-gold)' : 'transparent' }}; color: {{ $currentTab === 'diagnostic' ? 'var(--admin-gold)' : 'var(--admin-text-muted)' }};">
            Phòng Chẩn Đoán DNS Sandbox
        </a>
    </div>

    @if($currentTab === 'domains')
    <!-- TAB 1: DOMAINS LIST & OPERATIONS -->
    <div class="mayhen_admin_card" style="padding: 1rem 1.25rem; margin-bottom: 1rem;">
        <form action="{{ route('admin.settings.domains.index') }}" method="GET" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <input type="hidden" name="tab" value="domains">

            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                <span style="font-size: 0.85rem; font-weight: 600; color: var(--admin-charcoal);">Trạng thái:</span>
                <a href="{{ route('admin.settings.domains.index', array_filter(['tab' => 'domains', 'search' => request('search'), 'type' => request('type')])) }}" 
                   class="mayhen_admin_badge {{ empty(request('status')) ? 'mayhen_admin_badge--gold' : 'mayhen_admin_badge--neutral' }}" style="text-decoration: none; padding: 0.3rem 0.65rem;">
                    Tất cả
                </a>
                <a href="{{ route('admin.settings.domains.index', array_filter(['tab' => 'domains', 'status' => 'active', 'search' => request('search'), 'type' => request('type')])) }}" 
                   class="mayhen_admin_badge {{ request('status') === 'active' ? 'mayhen_admin_badge--success' : 'mayhen_admin_badge--neutral' }}" style="text-decoration: none; padding: 0.3rem 0.65rem;">
                    ● Hoạt Động
                </a>
                <a href="{{ route('admin.settings.domains.index', array_filter(['tab' => 'domains', 'status' => 'pending', 'search' => request('search'), 'type' => request('type')])) }}" 
                   class="mayhen_admin_badge {{ request('status') === 'pending' ? 'mayhen_admin_badge--warning' : 'mayhen_admin_badge--neutral' }}" style="text-decoration: none; padding: 0.3rem 0.65rem;">
                    ⏳ Chờ Xác Minh
                </a>
                <a href="{{ route('admin.settings.domains.index', array_filter(['tab' => 'domains', 'status' => 'failed', 'search' => request('search'), 'type' => request('type')])) }}" 
                   class="mayhen_admin_badge {{ request('status') === 'failed' ? 'mayhen_admin_badge--danger' : 'mayhen_admin_badge--neutral' }}" style="text-decoration: none; padding: 0.3rem 0.65rem;">
                    ✕ Lỗi DNS
                </a>
            </div>

            <div style="display: flex; gap: 0.5rem; width: 100%; max-width: 360px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm tên miền, subdomain, tên dâu rể..." 
                       class="mayhen_admin_form_input" style="flex: 1; padding: 0.5rem 0.75rem; border: 1px solid var(--admin-border-color); border-radius: 4px; font-size: 0.85rem;">
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="padding: 0.5rem 0.85rem; font-size: 0.85rem;">
                    Tìm
                </button>
                @if(request('search') || request('status') || request('type'))
                <a href="{{ route('admin.settings.domains.index', ['tab' => 'domains']) }}" class="mayhen_admin_btn mayhen_admin_btn--outline" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;" title="Xóa bộ lọc">
                    ✕
                </a>
                @endif
            </div>
        </form>
    </div>

    <div class="mayhen_admin_card" style="padding: 0; overflow: hidden; margin-bottom: 1.5rem;">
        <div class="mayhen_admin_table_container" style="overflow-x: auto;">
            <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--admin-border-color); text-align: left; background: var(--admin-bg-soft);">
                        <th style="padding: 0.75rem 1rem; color: var(--admin-text-muted); font-weight: 600;">Tên Miền / Định Tuyến</th>
                        <th style="padding: 0.75rem 1rem; color: var(--admin-text-muted); font-weight: 600;">Cặp Đôi & Thiệp Cưới</th>
                        <th style="padding: 0.75rem 1rem; color: var(--admin-text-muted); font-weight: 600;">Mục Tiêu CNAME</th>
                        <th style="padding: 0.75rem 1rem; color: var(--admin-text-muted); font-weight: 600; text-align: center;">Trạng Thái DNS</th>
                        <th style="padding: 0.75rem 1rem; color: var(--admin-text-muted); font-weight: 600; text-align: center;">Chứng Chỉ SSL</th>
                        <th style="padding: 0.75rem 1rem; color: var(--admin-text-muted); font-weight: 600;">Xác Minh Cuối</th>
                        <th style="padding: 0.75rem 1rem; color: var(--admin-text-muted); font-weight: 600; text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($domains as $item)
                    <tr style="border-bottom: 1px solid var(--admin-border-color);">
                        <td style="padding: 0.85rem 1rem;">
                            @if($item->custom_domain)
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <a href="https://{{ $item->custom_domain }}" target="_blank" style="font-weight: 700; color: #7C3AED; text-decoration: none;">
                                    {{ $item->custom_domain }} ↗
                                </a>
                                <span class="mayhen_admin_badge mayhen_admin_badge--gold" style="font-size: 0.65rem;">CUSTOM</span>
                            </div>
                            @endif

                            @if($item->subdomain)
                            <div style="font-size: 0.8rem; color: var(--admin-text-muted); margin-top: 2px;">
                                <a href="{{ $item->subdomainUrl() }}" target="_blank" style="color: var(--admin-text-muted); text-decoration: none;">
                                    {{ $item->subdomain }}.mayhen.vn ↗
                                </a>
                                <span class="mayhen_admin_badge mayhen_admin_badge--neutral" style="font-size: 0.65rem;">SUBDOMAIN</span>
                            </div>
                            @endif
                        </td>
                        <td style="padding: 0.85rem 1rem;">
                            @if($item->wedding)
                            <div style="font-weight: 600; color: var(--admin-charcoal);">
                                {{ $item->wedding->groom_name }} & {{ $item->wedding->bride_name }}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--admin-text-muted);">
                                {{ $item->wedding->user?->name ?: 'N/A' }}
                            </div>
                            @else
                            <span style="color: var(--admin-text-muted);">Chưa gán</span>
                            @endif
                        </td>
                        <td style="padding: 0.85rem 1rem; font-family: monospace; font-size: 0.8rem; color: #B45309;">
                            {{ $item->cname_target ?: $routingConfig['cname_target'] }}
                        </td>
                        <td style="padding: 0.85rem 1rem; text-align: center;">
                            @if($item->status === 'active')
                                <span class="mayhen_admin_badge mayhen_admin_badge--success" style="font-size: 0.75rem;">● Active</span>
                            @elseif($item->status === 'pending')
                                <span class="mayhen_admin_badge mayhen_admin_badge--warning" style="font-size: 0.75rem;">⏳ Pending</span>
                            @else
                                <span class="mayhen_admin_badge mayhen_admin_badge--danger" style="font-size: 0.75rem;">✕ Failed</span>
                            @endif
                        </td>
                        <td style="padding: 0.85rem 1rem; text-align: center;">
                            @if($item->ssl_status === 'active')
                                <span class="mayhen_admin_badge mayhen_admin_badge--success" style="font-size: 0.75rem;">🔒 HTTPS</span>
                            @elseif($item->ssl_status === 'pending')
                                <span class="mayhen_admin_badge mayhen_admin_badge--warning" style="font-size: 0.75rem;">⏳ Đang Cấp</span>
                            @else
                                <span class="mayhen_admin_badge mayhen_admin_badge--neutral" style="font-size: 0.75rem;">Chưa có</span>
                            @endif
                        </td>
                        <td style="padding: 0.85rem 1rem; white-space: nowrap; color: var(--admin-text-muted); font-size: 0.8rem;">
                            {{ $item->dns_verified_at ? $item->dns_verified_at->format('d/m/Y H:i') : 'Chưa xác minh' }}
                        </td>
                        <td style="padding: 0.85rem 1rem; text-align: right; white-space: nowrap;">
                            @if($item->custom_domain)
                            <form action="{{ route('admin.settings.domains.verify', $item) }}" method="POST" style="display: inline-block;">
                                @csrf
                                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--outline" style="padding: 0.35rem 0.65rem; font-size: 0.75rem;" title="Kiểm tra DNS CNAME">
                                    Xác Minh
                                </button>
                            </form>
                            @endif

                            @if($item->status !== 'active')
                            <form action="{{ route('admin.settings.domains.activate', $item) }}" method="POST" style="display: inline-block;">
                                @csrf
                                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="padding: 0.35rem 0.65rem; font-size: 0.75rem;" title="Cưỡng chế kích hoạt">
                                    Duyệt
                                </button>
                            </form>
                            @else
                            <form action="{{ route('admin.settings.domains.deactivate', $item) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Ngắt kết nối tên miền này?');">
                                @csrf
                                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--outline" style="padding: 0.35rem 0.65rem; font-size: 0.75rem; color: #D97706;" title="Ngắt kết nối">
                                    Tắt
                                </button>
                            </form>
                            @endif

                            <form action="{{ route('admin.settings.domains.destroy', $item) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản ghi tên miền này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--danger" style="padding: 0.35rem 0.65rem; font-size: 0.75rem;">
                                    Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 3rem; text-align: center; color: var(--admin-text-muted);">
                            Chưa tìm thấy bản ghi tên miền nào theo điều kiện lọc.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($domains->hasPages())
        <div style="padding: 1rem; border-top: 1px solid var(--admin-border-color);">
            {{ $domains->links() }}
        </div>
        @endif
    </div>
    @endif

    @if($currentTab === 'settings')
    <!-- TAB 2: PLATFORM ROUTING & DNS SETTINGS -->
    <div class="mayhen_admin_card" style="padding: 1.5rem; max-width: 800px; margin-bottom: 1.5rem;">
        <h3 style="margin: 0 0 0.5rem 0; font-size: 1.15rem; color: var(--admin-charcoal);">
            Cấu Hình Định Tuyến DNS Nền Tảng Mây Hẹn
        </h3>
        <p style="margin: 0 0 1.5rem 0; color: var(--admin-text-muted); font-size: 0.85rem;">
            Các thông số định danh DNS CNAME và địa chỉ IP gốc để hướng dẫn cặp đôi cấu hình tại các nhà đăng ký tên miền (iNET, Tenten, Matbao, Cloudflare, Namecheap).
        </p>

        <form action="{{ route('admin.settings.domains.settings') }}" method="POST">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--admin-charcoal);">
                    Mục Tiêu Bản Ghi CNAME Mặc Định (CNAME Target) <span style="color: #EF4444;">*</span>
                </label>
                <input type="text" name="cname_target" value="{{ $routingConfig['cname_target'] }}" 
                       class="mayhen_admin_form_input" style="width: 100%; padding: 0.6rem; border: 1px solid var(--admin-border-color); border-radius: 4px;" required>
                <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                    Bản ghi đích mà khách hàng bắt buộc phải tạo (ví dụ: CNAME trỏ về <code>cname.mayhen.vn</code>).
                </small>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--admin-charcoal);">
                    Địa Chỉ IPv4 Máy Chủ (A-Record Origin IP) <span style="color: #EF4444;">*</span>
                </label>
                <input type="text" name="server_ip" value="{{ $routingConfig['server_ip'] }}" 
                       class="mayhen_admin_form_input" style="width: 100%; padding: 0.6rem; border: 1px solid var(--admin-border-color); border-radius: 4px;" required>
                <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                    Dành cho trường hợp khách hàng muốn trỏ bản ghi A trực tiếp về IP máy chủ gốc.
                </small>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--admin-charcoal);">
                    Tên Miền Gốc Nền Tảng (Root Domain) <span style="color: #EF4444;">*</span>
                </label>
                <input type="text" name="root_domain" value="{{ $routingConfig['root_domain'] }}" 
                       class="mayhen_admin_form_input" style="width: 100%; padding: 0.6rem; border: 1px solid var(--admin-border-color); border-radius: 4px;" required>
                <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                    Domain mẹ của toàn bộ Vanity Subdomains (mặc định: <code>mayhen.vn</code>).
                </small>
            </div>

            <div style="margin-bottom: 1.5rem; background: var(--admin-bg-soft); padding: 1rem; border-radius: 6px;">
                <div style="font-weight: 600; font-size: 0.85rem; margin-bottom: 0.5rem; color: var(--admin-charcoal);">
                    Danh Sách Subdomain Hệ Thống Cấm Đăng Ký (Blacklisted Subdomains)
                </div>
                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                    @foreach($routingConfig['blacklisted_subdomains'] as $sub)
                    <span style="background: #fff; border: 1px solid var(--admin-border-color); padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-family: monospace; color: #B45309;">
                        {{ $sub }}
                    </span>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="padding: 0.65rem 1.5rem; font-size: 0.9rem;">
                Lưu Cấu Hình Định Tuyến
            </button>
        </form>
    </div>
    @endif

    @if($currentTab === 'diagnostic')
    <!-- TAB 3: DNS LOOKUP DIAGNOSTIC SANDBOX -->
    <div style="display: grid; grid-template-columns: 360px 1fr; gap: 1.5rem; align-items: flex-start;">
        <!-- Form Tra Cứu DNS -->
        <div class="mayhen_admin_card" style="padding: 1.5rem;">
            <h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; color: var(--admin-charcoal);">
                Chẩn Đoán DNS Thời Gian Thực
            </h3>
            <p style="margin: 0 0 1.25rem 0; color: var(--admin-text-muted); font-size: 0.85rem;">
                Nhập tên miền bất kỳ để kiểm tra các bản ghi CNAME, A-Record và phân tích khuyến nghị kỹ thuật.
            </p>

            <form action="{{ route('admin.settings.domains.lookup') }}" method="POST">
                @csrf

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--admin-charcoal);">
                        Hostname Cần Tra Cứu <span style="color: #EF4444;">*</span>
                    </label>
                    <input type="text" name="hostname" placeholder="Ví dụ: minhduc-lanchi.wedding" 
                           value="{{ $lookupResult['hostname'] ?? '' }}"
                           class="mayhen_admin_form_input" style="width: 100%; padding: 0.6rem; border: 1px solid var(--admin-border-color); border-radius: 4px;" required>
                    <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        Nhập tên miền đầy đủ không bao gồm https://
                    </small>
                </div>

                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="width: 100%; padding: 0.7rem; font-size: 0.9rem; font-weight: 600;">
                    🔍 Tra Cứu DNS Ngay
                </button>
            </form>
        </div>

        <!-- Bảng Kết Quả Chẩn Đoán -->
        <div class="mayhen_admin_card" style="padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin: 0; font-size: 1.1rem; color: var(--admin-charcoal);">
                    Kết Quả Chẩn Đoán Kỹ Thuật
                </h3>
                @if(!empty($lookupResult))
                <span class="mayhen_admin_badge {{ $lookupResult['status'] === 'matched' ? 'mayhen_admin_badge--success' : 'mayhen_admin_badge--danger' }}" style="font-size: 0.8rem;">
                    {{ $lookupResult['status'] === 'matched' ? '● CNAME Chính Xác' : '✕ Cấu Hình Chưa Khớp' }}
                </span>
                @endif
            </div>

            @if(!empty($lookupResult))
            <div style="background: var(--admin-bg-soft); padding: 1rem; border-radius: 6px; margin-bottom: 1.25rem; border-left: 3px solid {{ $lookupResult['status'] === 'matched' ? '#059669' : '#DC2626' }};">
                <div style="font-weight: 600; font-size: 0.9rem; color: var(--admin-charcoal); margin-bottom: 0.25rem;">
                    Hostname: {{ $lookupResult['hostname'] }} (Độ trễ: {{ $lookupResult['latency_ms'] }}ms)
                </div>
                <div style="font-size: 0.85rem; color: {{ $lookupResult['status'] === 'matched' ? '#059669' : '#DC2626' }};">
                    {{ $lookupResult['recommendation'] }}
                </div>
            </div>

            <!-- CNAME Records -->
            <div style="margin-bottom: 1.25rem;">
                <h4 style="margin: 0 0 0.5rem 0; font-size: 0.9rem; color: var(--admin-charcoal);">Bản Ghi CNAME</h4>
                @if(!empty($lookupResult['cname']))
                    <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <tr style="background: var(--admin-bg-soft);">
                            <th style="padding: 6px 10px;">Host</th>
                            <th style="padding: 6px 10px;">Type</th>
                            <th style="padding: 6px 10px;">Target</th>
                        </tr>
                        @foreach($lookupResult['cname'] as $c)
                        <tr>
                            <td style="padding: 6px 10px;">{{ $c['host'] ?? '' }}</td>
                            <td style="padding: 6px 10px; font-weight: 600;">CNAME</td>
                            <td style="padding: 6px 10px; font-family: monospace; color: #7C3AED;">{{ $c['target'] ?? '' }}</td>
                        </tr>
                        @endforeach
                    </table>
                @else
                    <div style="font-size: 0.8rem; color: var(--admin-text-muted); padding: 0.5rem; background: #fff; border: 1px dashed var(--admin-border-color); border-radius: 4px;">
                        Không tìm thấy bản ghi CNAME nào cho tên miền này.
                    </div>
                @endif
            </div>

            <!-- A Records -->
            <div style="margin-bottom: 1.25rem;">
                <h4 style="margin: 0 0 0.5rem 0; font-size: 0.9rem; color: var(--admin-charcoal);">Bản Ghi A (IPv4)</h4>
                @if(!empty($lookupResult['a_records']))
                    <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <tr style="background: var(--admin-bg-soft);">
                            <th style="padding: 6px 10px;">Host</th>
                            <th style="padding: 6px 10px;">Type</th>
                            <th style="padding: 6px 10px;">IP Address</th>
                        </tr>
                        @foreach($lookupResult['a_records'] as $a)
                        <tr>
                            <td style="padding: 6px 10px;">{{ $a['host'] ?? '' }}</td>
                            <td style="padding: 6px 10px; font-weight: 600;">A</td>
                            <td style="padding: 6px 10px; font-family: monospace; color: #059669;">{{ $a['ip'] ?? '' }}</td>
                        </tr>
                        @endforeach
                    </table>
                @else
                    <div style="font-size: 0.8rem; color: var(--admin-text-muted); padding: 0.5rem; background: #fff; border: 1px dashed var(--admin-border-color); border-radius: 4px;">
                        Không tìm thấy bản ghi A nào.
                    </div>
                @endif
            </div>

            <!-- NS Records -->
            <div>
                <h4 style="margin: 0 0 0.5rem 0; font-size: 0.9rem; color: var(--admin-charcoal);">Máy Chủ Phân Giải Tên Miền (NameServers)</h4>
                @if(!empty($lookupResult['ns_records']))
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        @foreach($lookupResult['ns_records'] as $ns)
                        <span style="background: #fff; border: 1px solid var(--admin-border-color); padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; font-family: monospace;">
                            {{ $ns['target'] ?? '' }}
                        </span>
                        @endforeach
                    </div>
                @else
                    <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Không tìm thấy thông tin NameServer.</div>
                @endif
            </div>

            @else
            <div style="padding: 3rem; text-align: center; color: var(--admin-text-muted);">
                Hãy nhập tên miền ở ô bên trái và bấm <strong>Tra Cứu DNS Ngay</strong> để bắt đầu chẩn đoán.
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

