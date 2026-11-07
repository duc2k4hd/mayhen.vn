@extends('partner.layouts.master')

@section('title', 'Hoa Hồng & Doanh Thu Đối Tác | ' . config('app.name'))

@section('content')
<div class="mayhen_partner_commissions_wrap">
    {{-- Breadcrumbs --}}
    <div class="mayhen_partner_breadcrumbs">
        <a href="{{ route('partner.dashboard') }}">Cổng Đối Tác</a>
        <span>/</span>
        <span>Hoa Hồng & Chiết Khấu</span>
    </div>

    <div class="mayhen_partner_sec_head" style="margin-bottom: 2rem;">
        <div>
            <h1 class="dash_head_title" style="font-size: 1.85rem;">Hoa Hồng & Chiết Khấu Doanh Thu</h1>
            <p class="dash_head_desc">
                Chính sách chiết khấu đối tác hiện tại của bạn: <strong style="color: #A68A56;">{{ $partner->commission_rate }}%</strong> trên mọi đơn hàng nâng cấp của khách hàng.
            </p>
        </div>
    </div>

    {{-- Financial Stat Cards --}}
    <div class="mayhen_partner_stats_grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 2rem;">
        <div class="mayhen_partner_stat_card">
            <div class="stat_card_top">
                <span class="stat_card_label">TỔNG HOA HỒNG TÍCH LŨY</span>
                <span class="stat_card_icon">📈</span>
            </div>
            <div class="stat_card_value" style="color: #2E7D32;">{{ number_format($totalEarned, 0, ',', '.') }} ₫</div>
            <span class="stat_card_hint">Tổng tiền hoa hồng đã ghi nhận</span>
        </div>

        <div class="mayhen_partner_stat_card">
            <div class="stat_card_top">
                <span class="stat_card_label">HOA HỒNG CHỜ DUYỆT</span>
                <span class="stat_card_icon">⏳</span>
            </div>
            <div class="stat_card_value" style="color: #E65100;">{{ number_format($totalPending, 0, ',', '.') }} ₫</div>
            <span class="stat_card_hint">Đang trong kỳ đối soát đơn hàng</span>
        </div>

        <div class="mayhen_partner_stat_card">
            <div class="stat_card_top">
                <span class="stat_card_label">SỐ DƯ KHẢ DỤNG</span>
                <span class="stat_card_icon">🏦</span>
            </div>
            <div class="stat_card_value" style="color: #A68A56;">{{ $partner->formatted_balance }}</div>
            <span class="stat_card_hint">Có thể yêu cầu thanh toán</span>
        </div>
    </div>

    {{-- Commissions History Table --}}
    <div class="mayhen_partner_table_card">
        @if($commissions->isNotEmpty())
            <div style="overflow-x: auto;">
                <table class="mayhen_partner_table">
                    <thead>
                        <tr>
                            <th>Thời Gian</th>
                            <th>Đám Cưới Khách Hàng</th>
                            <th>Mã Đơn Hàng</th>
                            <th>Loại Giao Dịch</th>
                            <th>Trạng Thái</th>
                            <th style="text-align: right;">Số Tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commissions as $c)
                            <tr>
                                <td>
                                    {{ $c->created_at ? $c->created_at->format('H:i d/m/Y') : '—' }}
                                </td>
                                <td>
                                    @if($c->wedding)
                                        <strong style="color: #1F1D1A;">{{ $c->wedding->groom_name }} & {{ $c->wedding->bride_name }}</strong>
                                    @else
                                        <span style="color: #8C8479;">Chiết khấu toàn kỳ</span>
                                    @endif
                                </td>
                                <td>
                                    @if($c->order)
                                        <span style="font-family: monospace; color: #544E47;">#{{ $c->order->order_number ?? $c->order->id }}</span>
                                    @else
                                        <span style="color: #A39B8F;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size: 0.82rem; font-weight: 600;">
                                        {{ $c->type === 'payout' ? 'Rút Tiền / Quyết Toán' : ($c->type === 'bonus' ? 'Thưởng Đối Tác' : 'Hoa Hồng Thiệp') }}
                                    </span>
                                </td>
                                <td>
                                    @if($c->status === 'approved' || $c->status === 'paid')
                                        <span class="badge_status badge_published">{{ $c->status_label }}</span>
                                    @else
                                        <span class="badge_status badge_draft">{{ $c->status_label }}</span>
                                    @endif
                                </td>
                                <td style="text-align: right; font-weight: 700; font-size: 0.95rem; color: {{ $c->type === 'payout' ? '#C62828' : '#2E7D32' }};">
                                    {{ $c->formatted_amount }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($commissions->hasPages())
                <div style="padding: 1.25rem; display: flex; justify-content: center; border-top: 1px solid #EBE5DC;">
                    {{ $commissions->links() }}
                </div>
            @endif
        @else
            <div class="mayhen_partner_empty">
                <div class="empty_icon">💰</div>
                <h3 class="empty_title">Chưa Có Giao Dịch Hoa Hồng</h3>
                <p class="empty_desc">
                    Khi khách hàng của Studio nâng cấp các gói thiệp cao cấp (Gói Lụa, Gói Hoàng Gia), hoa hồng sẽ tự động được cộng vào số dư này.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection

