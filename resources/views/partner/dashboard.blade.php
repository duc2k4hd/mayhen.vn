@extends('partner.layouts.master')

@section('title', 'Bảng Điều Khiển Studio: ' . $partner->business_name . ' | ' . config('app.name'))

@section('content')
<div class="mayhen_partner_dash_wrap">
    @if(session('success'))
        <div class="mayhen_partner_alert mayhen_partner_alert--success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('info'))
        <div class="mayhen_partner_alert mayhen_partner_alert--info">
            {{ session('info') }}
        </div>
    @endif

    {{-- Top Header Section --}}
    <header class="mayhen_partner_dash_head">
        <div class="dash_head_info">
            <span class="studio_badge">⚜️ ĐỐI TÁC {{ strtoupper($partner->tier_label) }}</span>
            <h1 class="dash_head_title">{{ $partner->business_name }}</h1>
            <p class="dash_head_desc">
                Cổng quản lý thiệp cưới cao cấp & điều phối khách hàng dành cho Studio & Wedding Planner.
            </p>
        </div>
        <div class="dash_head_actions">
            <a href="{{ route('partner.weddings.create') }}" class="mayhen_partner_btn mayhen_partner_btn--gold">
                + Khởi Tạo Thiệp Mới
            </a>
            <a href="{{ route('partner.profile') }}" class="mayhen_partner_btn mayhen_partner_btn--outline">
                ⚙️ Cài Đặt Studio
            </a>
        </div>
    </header>

    {{-- 4 Big Metric Cards --}}
    <section class="mayhen_partner_stats_grid">
        <div class="mayhen_partner_stat_card">
            <div class="stat_card_top">
                <span class="stat_card_label">KHÁCH HÀNG ĐANG QUẢN LÝ</span>
                <span class="stat_card_icon">💍</span>
            </div>
            <div class="stat_card_value">{{ number_format($totalWeddingsCount) }}</div>
            <span class="stat_card_hint">Đám cưới đã khởi tạo</span>
        </div>

        <div class="mayhen_partner_stat_card">
            <div class="stat_card_top">
                <span class="stat_card_label">THIỆP ĐANG XUẤT BẢN</span>
                <span class="stat_card_icon">🌐</span>
            </div>
            <div class="stat_card_value" style="color: #2E7D32;">{{ number_format($totalPublishedCount) }}</div>
            <span class="stat_card_hint">Đang phát hành trực tuyến</span>
        </div>

        <div class="mayhen_partner_stat_card">
            <div class="stat_card_top">
                <span class="stat_card_label">TỔNG KHÁCH ĐÃ RSVP</span>
                <span class="stat_card_icon">👥</span>
            </div>
            <div class="stat_card_value" style="color: #0277BD;">{{ number_format($totalGuestsAttended) }}</div>
            <span class="stat_card_hint">Xác nhận có mặt tại tiệc</span>
        </div>

        <div class="mayhen_partner_stat_card">
            <div class="stat_card_top">
                <span class="stat_card_label">SỐ DƯ HOA HỒNG ({{ $partner->commission_rate }}%)</span>
                <span class="stat_card_icon">💰</span>
            </div>
            <div class="stat_card_value" style="color: #A68A56;">{{ $partner->formatted_balance }}</div>
            <span class="stat_card_hint"><a href="{{ route('partner.commissions.index') }}" style="color: #8C6D3F; text-decoration: none;">Xem chi tiết ↗</a></span>
        </div>
    </section>

    {{-- Partner Code & Referral Box --}}
    <section class="mayhen_partner_code_banner">
        <div class="banner_code_info">
            <h3>Mã Đối Tác Độc Quyền & Co-Branding</h3>
            <p>Khách hàng nhập mã này khi nâng cấp gói dịch vụ để Studio nhận ngay {{ $partner->commission_rate }}% hoa hồng tự động.</p>
        </div>
        <div class="banner_code_box">
            <span class="code_pill" id="partner_code_val">{{ $partner->partner_code }}</span>
            <button type="button" class="mayhen_partner_btn mayhen_partner_btn--outline" data-copy-target="#partner_code_val">
                📋 Sao Chép Mã
            </button>
        </div>
    </section>

    {{-- Recent Weddings Table --}}
    <section class="mayhen_partner_section">
        <div class="mayhen_partner_sec_head">
            <h2 class="sec_head_title">Thiệp Cưới Khách Hàng Gần Đây</h2>
            <a href="{{ route('partner.weddings.index') }}" class="mayhen_partner_btn mayhen_partner_btn--sm mayhen_partner_btn--outline">
                Xem Tất Cả ({{ $totalWeddingsCount }}) ↗
            </a>
        </div>

        <div class="mayhen_partner_table_card">
            @if($recentWeddings->isNotEmpty())
                <div style="overflow-x: auto;">
                    <table class="mayhen_partner_table">
                        <thead>
                            <tr>
                                <th>Cặp Đôi Khách Hàng</th>
                                <th>Ngày Cưới</th>
                                <th>Mẫu Thiệp</th>
                                <th>Trạng Thái</th>
                                <th>Mã Bàn Giao</th>
                                <th style="text-align: right;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentWeddings as $w)
                                @php
                                    $inv = $w->invitations->first();
                                @endphp
                                <tr>
                                    <td>
                                        <strong style="display: block; font-size: 0.95rem; color: #1F1D1A;">
                                            {{ $w->groom_name }} & {{ $w->bride_name }}
                                        </strong>
                                        @if($w->venue_name)
                                            <span style="font-size: 0.78rem; color: #8C8479;">📍 {{ $w->venue_name }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $w->wedding_date ? $w->wedding_date->format('d/m/Y') : 'Chưa đặt' }}
                                    </td>
                                    <td>
                                        <span style="font-weight: 600; color: #544E47;">
                                            {{ $inv?->template?->name ?? 'Mặc định' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($inv && $inv->is_published)
                                            <span class="badge_status badge_published">Đã Xuất Bản</span>
                                        @else
                                            <span class="badge_status badge_draft">Bản Nháp</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($w->handover_code)
                                            <span style="font-family: monospace; font-weight: 700; color: #8C6D3F; background: #FAF8F5; padding: 2px 6px; border-radius: 4px; border: 1px solid #EBE5DC;">
                                                {{ $w->handover_code }}
                                            </span>
                                        @else
                                            <span style="font-size: 0.78rem; color: #2E7D32;">✓ Đã nhận thiệp</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="table_actions" style="justify-content: flex-end;">
                                            @if($inv)
                                                <a href="{{ route('invitation.create', ['edit' => $inv->slug]) }}" class="mayhen_partner_btn mayhen_partner_btn--sm mayhen_partner_btn--primary" title="Vào Studio chỉnh sửa">
                                                    ✏️ Sửa
                                                </a>
                                                <a href="{{ route('invitation.show', $inv->slug) }}" target="_blank" class="mayhen_partner_btn mayhen_partner_btn--sm mayhen_partner_btn--outline" title="Xem thiệp">
                                                    👁️
                                                </a>
                                            @endif
                                            <a href="{{ route('partner.weddings.handover', $w->id) }}" class="mayhen_partner_btn mayhen_partner_btn--sm mayhen_partner_btn--outline" title="Bàn giao cho cặp đôi">
                                                🔑 Bàn Giao
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="mayhen_partner_empty">
                    <div class="empty_icon">💌</div>
                    <h3 class="empty_title">Chưa Có Thiệp Cưới Nào</h3>
                    <p class="empty_desc">
                        Bắt đầu tạo thiệp cưới số đầu tiên cho khách hàng của bạn để trải nghiệm tính năng bàn giao thông minh.
                    </p>
                    <a href="{{ route('partner.weddings.create') }}" class="mayhen_partner_btn mayhen_partner_btn--gold">
                        + Tạo Thiệp Cho Khách Hàng
                    </a>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection

