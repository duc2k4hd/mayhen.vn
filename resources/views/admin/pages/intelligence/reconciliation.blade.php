@extends('admin.layouts.master')

@section('title', 'Thám Tử Đối Soát Tài Chính — Mây Hẹn')
@section('page_title', 'Thám Tử Đối Soát Tài Chính (Finance Reconciliation Detective)')

@section('content')
    {{-- KPI Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Tổng Đơn Đối Soát
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                {{ number_format($kpis['total'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Đơn hàng phát sinh gần nhất</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #27AE60;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Khớp Lệnh Tuyệt Đối (100%)
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #27AE60;">
                {{ number_format($kpis['matched'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #27AE60; margin-top: 4px; font-weight: 600;">Số tiền thực nhận = Giá gói</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #C0392B;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Chuyển Thiếu Tiền (Underpaid)
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #C0392B;">
                {{ number_format($kpis['underpaid'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #C0392B; margin-top: 4px; font-weight: 600;">Thực nhận < Kỳ vọng</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #F39C12;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Chuyển Thừa Tiền (Overpaid)
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #F39C12;">
                {{ number_format($kpis['overpaid'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #F39C12; margin-top: 4px; font-weight: 600;">Thực nhận > Kỳ vọng</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #7F8C8D;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Chờ Khớp Lệnh
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #7F8C8D;">
                {{ number_format($kpis['pending_match'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Đơn chưa nhận tiền</div>
        </div>
    </div>

    {{-- Bộ Lọc Trạng Thái --}}
    <div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
        <a href="{{ route('admin.intelligence.reconciliation') }}"
           class="mayhen_admin_btn {{ empty($currentStatus) ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn--ghost' }}"
           style="padding: 8px 16px; font-size: 0.85rem;">
            Tất cả đơn ({{ $kpis['total'] }})
        </a>
        <a href="{{ route('admin.intelligence.reconciliation', ['status' => 'matched']) }}"
           class="mayhen_admin_btn {{ $currentStatus === 'matched' ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn--ghost' }}"
           style="padding: 8px 16px; font-size: 0.85rem; color: {{ $currentStatus === 'matched' ? '#fff' : '#27AE60' }};">
            ✓ Khớp lệnh ({{ $kpis['matched'] }})
        </a>
        <a href="{{ route('admin.intelligence.reconciliation', ['status' => 'underpaid']) }}"
           class="mayhen_admin_btn {{ $currentStatus === 'underpaid' ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn--ghost' }}"
           style="padding: 8px 16px; font-size: 0.85rem; color: {{ $currentStatus === 'underpaid' ? '#fff' : '#C0392B' }};">
            ⚠️ Chuyển thiếu ({{ $kpis['underpaid'] }})
        </a>
        <a href="{{ route('admin.intelligence.reconciliation', ['status' => 'overpaid']) }}"
           class="mayhen_admin_btn {{ $currentStatus === 'overpaid' ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn--ghost' }}"
           style="padding: 8px 16px; font-size: 0.85rem; color: {{ $currentStatus === 'overpaid' ? '#fff' : '#F39C12' }};">
            ⚡ Chuyển thừa ({{ $kpis['overpaid'] }})
        </a>
        <a href="{{ route('admin.intelligence.reconciliation', ['status' => 'pending_match']) }}"
           class="mayhen_admin_btn {{ $currentStatus === 'pending_match' ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn--ghost' }}"
           style="padding: 8px 16px; font-size: 0.85rem; color: {{ $currentStatus === 'pending_match' ? '#fff' : '#7F8C8D' }};">
            ⏳ Chờ khớp ({{ $kpis['pending_match'] }})
        </a>
    </div>

    {{-- Bảng Đối Soát Chi Tiết --}}
    <div class="mayhen_admin_card" style="padding: 0; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--mh-admin-border); display: flex; align-items: center; justify-content: space-between;">
            <span style="font-weight: 700; font-size: 0.875rem;">{{ $items->count() }} đơn hàng trong sổ đối soát</span>
            <a href="{{ route('admin.orders.webhooks') }}" class="mayhen_admin_btn mayhen_admin_btn--ghost" style="padding: 4px 10px; font-size: 0.75rem;">
                Xem Nhật Ký Webhook Thô &rarr;
            </a>
        </div>

        @if($items->isEmpty())
            <div style="padding: 48px; text-align: center; color: var(--mh-admin-muted);">
                <div style="font-size: 2rem; margin-bottom: 12px;">📊</div>
                <div style="font-weight: 600;">Không có dữ liệu đối soát phù hợp.</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="mayhen_admin_table">
                    <thead>
                        <tr>
                            <th>Mã Đơn Hàng</th>
                            <th>Khách Hàng</th>
                            <th>Gói Dịch Vụ</th>
                            <th>Số Tiền Kỳ Vọng</th>
                            <th>Thực Nhận (Webhook)</th>
                            <th>Chênh Lệch</th>
                            <th>Trạng Thái Đối Soát</th>
                            <th>Thời Gian</th>
                            <th style="text-align: right;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $entry)
                            @php
                                $order = $entry['order'];
                                $badgeClass = match($entry['status']) {
                                    'matched' => 'mayhen_admin_badge--success',
                                    'underpaid' => 'mayhen_admin_badge--danger',
                                    'overpaid' => 'mayhen_admin_badge--warning',
                                    default => 'mayhen_admin_badge--neutral',
                                };
                                $statusLabel = match($entry['status']) {
                                    'matched' => 'Khớp 100%',
                                    'underpaid' => 'Thiếu tiền',
                                    'overpaid' => 'Thừa tiền',
                                    default => 'Chờ khớp',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <code style="font-size: 0.75rem; background: #f4f1ec; padding: 2px 6px; border-radius: 4px;">{{ $order->order_code }}</code>
                                </td>
                                <td>
                                    <div style="font-weight: 600; font-size: 0.85rem;">{{ $order->user?->name ?? '—' }}</div>
                                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">{{ $order->user?->email ?? '' }}</div>
                                </td>
                                <td>{{ $order->plan?->name ?? 'Gói cước' }}</td>
                                <td style="font-weight: 600;">{{ number_format($entry['expected_amount'], 0, ',', '.') }}đ</td>
                                <td style="font-weight: 600; color: {{ $entry['diff'] < 0 ? '#C0392B' : ($entry['diff'] > 0 ? '#F39C12' : '#27AE60') }};">
                                    {{ number_format($entry['paid_amount'], 0, ',', '.') }}đ
                                </td>
                                <td>
                                    @if($entry['diff'] === 0)
                                        <span style="color: #27AE60; font-size: 0.8rem; font-weight: 600;">0đ</span>
                                    @elseif($entry['diff'] < 0)
                                        <span style="color: #C0392B; font-size: 0.8rem; font-weight: 700;">{{ number_format($entry['diff'], 0, ',', '.') }}đ</span>
                                    @else
                                        <span style="color: #F39C12; font-size: 0.8rem; font-weight: 700;">+{{ number_format($entry['diff'], 0, ',', '.') }}đ</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="mayhen_admin_badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td style="font-size: 0.8rem; white-space: nowrap;">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <a href="{{ route('admin.orders.show', $order->id) }}"
                                       class="mayhen_admin_btn mayhen_admin_btn--ghost"
                                       style="padding: 4px 8px; font-size: 0.75rem;">
                                        Xem đơn
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

