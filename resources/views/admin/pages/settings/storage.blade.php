@extends('admin.layouts.master')

@section('title', 'Thanh Tra Bộ Nhớ Lưu Trữ')
@section('page_title', 'Thanh Tra Bộ Nhớ & Tối Ưu Tệp Lưu Trữ')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/settings.css') }}">
@endpush

@section('content')
    @php
        $totalMB = round($totalBytes / (1024 * 1024), 2);
        $totalGB = round($totalBytes / (1024 * 1024 * 1024), 2);
        $displayTotal = $totalMB > 1024 ? "{$totalGB} GB" : "{$totalMB} MB";
        
        $weddingsPct = $totalBytes > 0 ? round(($categoryStats['weddings']['bytes'] / $totalBytes) * 100, 1) : 0;
        $clientPct = $totalBytes > 0 ? round(($categoryStats['client']['bytes'] / $totalBytes) * 100, 1) : 0;
        $adminPct = $totalBytes > 0 ? round(($categoryStats['admin']['bytes'] / $totalBytes) * 100, 1) : 0;
        $otherPct = $totalBytes > 0 ? max(0, 100 - ($weddingsPct + $clientPct + $adminPct)) : 0;
    @endphp

    <!-- Metric Cards -->
    <div class="mayhen_admin_grid_cards" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="display: flex; align-items: center; gap: 16px; padding: 20px;">
            <div class="mayhen_admin_kpi_icon" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background-color: #FBF7EE; color: var(--mh-admin-gold); border: 1px solid #EFE4CA;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                </svg>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--mh-admin-charcoal);">{{ $displayTotal }}</div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Tổng Dung Lượng Đã Dùng</div>
            </div>
        </div>

        <div class="mayhen_admin_card" style="display: flex; align-items: center; gap: 16px; padding: 20px;">
            <div class="mayhen_admin_kpi_icon" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background-color: #E8F5E9; color: #1B5E20; border: 1px solid #C8E6C9;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #1B5E20;">{{ number_format($totalFiles) }}</div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Tổng Số Tệp Tin Lưu Trữ</div>
            </div>
        </div>

        <div class="mayhen_admin_card" style="display: flex; align-items: center; gap: 16px; padding: 20px;">
            <div class="mayhen_admin_kpi_icon" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background-color: #FFF8E1; color: #B78103; border: 1px solid #FFE082;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                </svg>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #B78103;">{{ number_format($categoryStats['weddings']['count']) }}</div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Ảnh Cưới Khách Hàng</div>
            </div>
        </div>
    </div>

    <!-- Visual Storage Distribution Card -->
    <div class="mayhen_admin_card" style="padding: 24px; margin-bottom: 24px;">
        <h4 style="font-size: 1rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-bottom: 4px;">
            Phân Bổ Dung Lượng Bộ Nhớ (public storage)
        </h4>
        <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">
            Thống kê không gian lưu trữ theo từng phân hệ trong thư mục <code>storage/app/public/</code>
        </div>

        <!-- Progress Bar -->
        <div class="mayhen_admin_storage_bar">
            <div class="mayhen_admin_storage_segment weddings" style="width: {{ $weddingsPct }}%;" title="Ảnh cưới: {{ $weddingsPct }}%"></div>
            <div class="mayhen_admin_storage_segment client" style="width: {{ $clientPct }}%;" title="Client/Nhạc: {{ $clientPct }}%"></div>
            <div class="mayhen_admin_storage_segment admin" style="width: {{ $adminPct }}%;" title="Admin: {{ $adminPct }}%"></div>
            <div class="mayhen_admin_storage_segment other" style="width: {{ $otherPct }}%;" title="Khác: {{ $otherPct }}%"></div>
        </div>

        <!-- Legend -->
        <div class="mayhen_admin_storage_legend">
            <div class="mayhen_admin_storage_legend_item">
                <span class="mayhen_admin_storage_dot" style="background-color: #C5A880;"></span>
                <span><strong>Ảnh Cưới (weddings/):</strong> {{ round($categoryStats['weddings']['bytes'] / (1024*1024), 2) }} MB ({{ $weddingsPct }}%)</span>
            </div>
            <div class="mayhen_admin_storage_legend_item">
                <span class="mayhen_admin_storage_dot" style="background-color: #3B82F6;"></span>
                <span><strong>Giao diện & Nhạc (client/):</strong> {{ round($categoryStats['client']['bytes'] / (1024*1024), 2) }} MB ({{ $clientPct }}%)</span>
            </div>
            <div class="mayhen_admin_storage_legend_item">
                <span class="mayhen_admin_storage_dot" style="background-color: #8B5CF6;"></span>
                <span><strong>Admin (admin/):</strong> {{ round($categoryStats['admin']['bytes'] / (1024*1024), 2) }} MB ({{ $adminPct }}%)</span>
            </div>
            <div class="mayhen_admin_storage_legend_item">
                <span class="mayhen_admin_storage_dot" style="background-color: #94A3B8;"></span>
                <span><strong>Tệp khác:</strong> {{ round($categoryStats['other']['bytes'] / (1024*1024), 2) }} MB ({{ $otherPct }}%)</span>
            </div>
        </div>
    </div>

    <!-- Recent Files Table -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header">
            <div class="mayhen_admin_card_title">Tệp Tin Tải Lên Gần Đây Nhất (20 Tệp)</div>
        </div>

        <div class="mayhen_admin_table_wrapper">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th>Đường Dẫn Tệp</th>
                        <th style="width: 140px; text-align: right;">Kích Thước</th>
                        <th style="width: 160px; text-align: right;">Thời Gian Tạo</th>
                        <th style="width: 110px; text-align: center;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentFiles as $f)
                        @php
                            $sizeKB = round($f['size'] / 1024, 1);
                            $displaySize = $sizeKB > 1024 ? round($sizeKB / 1024, 2) . ' MB' : $sizeKB . ' KB';
                        @endphp
                        <tr>
                            <td style="font-family: monospace; font-size: 0.8125rem; color: var(--mh-admin-charcoal);">
                                {{ $f['path'] }}
                            </td>
                            <td style="text-align: right; font-size: 0.8125rem; font-weight: 600;">
                                {{ $displaySize }}
                            </td>
                            <td style="text-align: right; font-size: 0.8125rem; color: var(--mh-admin-muted);">
                                {{ date('d/m/Y H:i', $f['last_modified']) }}
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ $f['url'] }}" target="_blank" rel="noopener noreferrer" class="mayhen_admin_btn mayhen_admin_btn_sm mayhen_admin_btn_outline">
                                    <span>Xem &nearr;</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 36px; color: var(--mh-admin-muted);">
                                Thư mục lưu trữ công khai hiện chưa có tệp tin nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

