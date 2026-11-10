@extends('admin.layouts.master')

@section('title', 'Quản Trị Mạng Lưới Đối Tác & Studio — Mây Hẹn Admin')

@section('content')
<div class="mayhen_admin_page">
    {{-- Header --}}
    <div class="mayhen_admin_page_header">
        <div>
            <div class="mayhen_admin_breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Tổng quan</a>
                <span>/</span>
                <span>Đối tác & Studio</span>
            </div>
            <h1 class="mayhen_admin_page_title">Mạng Lưới Đối Tác & Studio</h1>
            <p class="mayhen_admin_page_subtitle">Quản trị các Studio ảnh cưới, Wedding Planner, chính sách hoa hồng B2B và quyết toán tài chính.</p>
        </div>
        <div class="mayhen_admin_page_actions">
            <a href="{{ route('admin.partners.commissions') }}" class="mayhen_admin_btn mayhen_admin_btn--outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                Sổ Quyết Toán Hoa Hồng
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="mayhen_admin_alert mayhen_admin_alert--success">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mayhen_admin_alert mayhen_admin_alert--danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="mayhen_admin_stats_grid">
        <div class="mayhen_admin_stat_card">
            <span class="mayhen_admin_stat_label">Tổng số Đối tác Studio</span>
            <div class="mayhen_admin_stat_value">{{ number_format($metrics['total_partners']) }}</div>
            <div class="mayhen_admin_stat_meta">Mạng lưới Agency / Planner</div>
        </div>

        <div class="mayhen_admin_stat_card">
            <span class="mayhen_admin_stat_label">Đang Hoạt Động (Active)</span>
            <div class="mayhen_admin_stat_value" style="color: #2E7D32;">{{ number_format($metrics['active_partners']) }}</div>
            <div class="mayhen_admin_stat_meta">{{ $metrics['pending_partners'] }} hồ sơ chờ duyệt</div>
        </div>

        <div class="mayhen_admin_stat_card">
            <span class="mayhen_admin_stat_label">Đám Cưới Do Đối Tác Quản Lý</span>
            <div class="mayhen_admin_stat_value">{{ number_format($metrics['total_weddings']) }}</div>
            <div class="mayhen_admin_stat_meta">Dự án thiệp cưới B2B</div>
        </div>

        <div class="mayhen_admin_stat_card">
            <span class="mayhen_admin_stat_label">Nợ Ví Hoa Hồng Hiện Tại</span>
            <div class="mayhen_admin_stat_value" style="color: #9C7A4A;">{{ number_format($metrics['total_wallet_liability'], 0, ',', '.') }} ₫</div>
            <div class="mayhen_admin_stat_meta">Đã chi: {{ number_format($metrics['total_commissions_paid'], 0, ',', '.') }} ₫</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="mayhen_admin_card mayhen_admin_mb_4">
        <form method="GET" action="{{ route('admin.partners.index') }}" class="mayhen_admin_filter_form" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 240px;">
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Tìm theo tên studio, mã đối tác, email, SĐT..." class="mayhen_admin_input" style="width: 100%;">
            </div>

            <div style="width: 160px;">
                <select name="status" class="mayhen_admin_select" style="width: 100%;">
                    <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                    <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Chờ phê duyệt</option>
                    <option value="suspended" {{ $filters['status'] === 'suspended' ? 'selected' : '' }}>Đã đình chỉ</option>
                </select>
            </div>

            <div style="width: 150px;">
                <select name="tier" class="mayhen_admin_select" style="width: 100%;">
                    <option value="all" {{ $filters['tier'] === 'all' ? 'selected' : '' }}>Tất cả hạng</option>
                    <option value="silver" {{ $filters['tier'] === 'silver' ? 'selected' : '' }}>Hạng Silver</option>
                    <option value="gold" {{ $filters['tier'] === 'gold' ? 'selected' : '' }}>Hạng Gold</option>
                    <option value="platinum" {{ $filters['tier'] === 'platinum' ? 'selected' : '' }}>Hạng Platinum</option>
                </select>
            </div>

            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary">Lọc</button>
            @if(!empty($filters['search']) || $filters['status'] !== 'all' || $filters['tier'] !== 'all')
                <a href="{{ route('admin.partners.index') }}" class="mayhen_admin_btn mayhen_admin_btn--ghost">Xóa lọc</a>
            @endif
        </form>
    </div>

    {{-- Partners Table --}}
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_table_wrapper">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th>Mã & Tên Đối Tác</th>
                        <th>Chủ Tài Khoản</th>
                        <th>Hạng & Tỷ Lệ</th>
                        <th>Đám Cưới</th>
                        <th>Số Dư Ví</th>
                        <th>Trạng Thái</th>
                        <th>Ngày Gia Nhập</th>
                        <th style="text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($partners as $partner)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #1F1D1A; font-size: 15px;">{{ $partner->business_name }}</div>
                                <div style="font-family: monospace; font-size: 12px; color: #9C7A4A;">{{ $partner->partner_code }}</div>
                            </td>
                            <td>
                                <div style="font-size: 14px; color: #1F1D1A;">{{ $partner->user ? $partner->user->name : 'N/A' }}</div>
                                <div style="font-size: 12px; color: #8C827A;">{{ $partner->email }} • {{ $partner->phone }}</div>
                            </td>
                            <td>
                                <span class="mayhen_admin_badge mayhen_admin_badge--{{ $partner->tier === 'platinum' ? 'gold' : ($partner->tier === 'gold' ? 'warning' : 'info') }}">
                                    {{ strtoupper($partner->tier) }}
                                </span>
                                <div style="font-size: 12px; color: #555; margin-top: 4px;">{{ $partner->commission_rate }}% chiết khấu</div>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: #1F1D1A;">{{ $partner->weddings_count }}</span>
                                <span style="font-size: 12px; color: #8C827A;">dự án</span>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #9C7A4A;">{{ number_format($partner->wallet_balance, 0, ',', '.') }} ₫</div>
                                <div style="font-size: 11px; color: #8C827A;">{{ $partner->commissions_count }} giao dịch</div>
                            </td>
                            <td>
                                @if($partner->status === 'active')
                                    <span class="mayhen_admin_badge mayhen_admin_badge--success">Hoạt động</span>
                                @elseif($partner->status === 'pending')
                                    <span class="mayhen_admin_badge mayhen_admin_badge--warning">Chờ duyệt</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge--danger">Đã đình chỉ</span>
                                @endif
                            </td>
                            <td style="font-size: 13px; color: #8C827A;">
                                {{ $partner->created_at->format('d/m/Y') }}
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('admin.partners.show', $partner->id) }}" class="mayhen_admin_btn mayhen_admin_btn--sm mayhen_admin_btn--outline">
                                        Chi tiết
                                    </a>

                                    @if($partner->status === 'pending')
                                        <form method="POST" action="{{ route('admin.partners.approve', $partner->id) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--sm mayhen_admin_btn--success" onclick="return confirm('Xác nhận phê duyệt đối tác này?')">
                                                Duyệt
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #8C827A;">
                                Chưa có đối tác nào phù hợp với bộ lọc tìm kiếm.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($partners->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #EAE3D9;">
                {{ $partners->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

