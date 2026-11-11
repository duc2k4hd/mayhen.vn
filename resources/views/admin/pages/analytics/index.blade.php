@extends('admin.layouts.master')

@section('title', 'Trung Tâm Phân Tích & Báo Cáo Toàn Diện')

@section('content')
<div class="mayhen_admin_page_header" style="margin-bottom: 24px;">
    <div class="mayhen_admin_page_header_left">
        <h1 class="mayhen_admin_page_title">Trung Tâm Phân Tích & Báo Cáo Toàn Diện</h1>
        <p class="mayhen_admin_page_subtitle">Đo lường tăng trưởng doanh thu, hành trình tạo thiệp, hiệu suất mẫu và mức độ phản hồi RSVP (Mục 19 & 49 PRD V3)</p>
    </div>
    <div class="mayhen_admin_page_header_right" style="display: flex; gap: 10px; align-items: center;">
        <a href="{{ route('admin.analytics.export', ['tab' => $activeTab, 'range' => $range, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="font-size: 13px; padding: 8px 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Xuất Báo Cáo {{ strtoupper($activeTab) }} (CSV)
        </a>
    </div>
</div>

<!-- BỘ LỌC KHOẢNG THỜI GIAN (DATE RANGE SELECTOR) -->
<div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 14px 18px; margin-bottom: 24px;">
    <form method="GET" action="{{ route('admin.analytics.index') }}" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between;">
        <input type="hidden" name="tab" value="{{ $activeTab }}">
        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #7A7265; letter-spacing: 0.05em;">KHOẢNG THỜI GIAN:</span>
            
            <a href="{{ route('admin.analytics.index', ['tab' => $activeTab, 'range' => '7_days']) }}" 
               class="mayhen_admin_badge {{ $range === '7_days' ? 'mayhen_admin_badge--primary' : 'mayhen_admin_badge--secondary' }}" 
               style="text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 12px;">
                7 Ngày Qua
            </a>

            <a href="{{ route('admin.analytics.index', ['tab' => $activeTab, 'range' => '30_days']) }}" 
               class="mayhen_admin_badge {{ $range === '30_days' ? 'mayhen_admin_badge--primary' : 'mayhen_admin_badge--secondary' }}" 
               style="text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 12px;">
                30 Ngày Qua
            </a>

            <a href="{{ route('admin.analytics.index', ['tab' => $activeTab, 'range' => 'this_month']) }}" 
               class="mayhen_admin_badge {{ $range === 'this_month' ? 'mayhen_admin_badge--primary' : 'mayhen_admin_badge--secondary' }}" 
               style="text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 12px;">
                Tháng Này
            </a>

            <a href="{{ route('admin.analytics.index', ['tab' => $activeTab, 'range' => 'last_month']) }}" 
               class="mayhen_admin_badge {{ $range === 'last_month' ? 'mayhen_admin_badge--primary' : 'mayhen_admin_badge--secondary' }}" 
               style="text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 12px;">
                Tháng Trước
            </a>

            <a href="{{ route('admin.analytics.index', ['tab' => $activeTab, 'range' => '90_days']) }}" 
               class="mayhen_admin_badge {{ $range === '90_days' ? 'mayhen_admin_badge--primary' : 'mayhen_admin_badge--secondary' }}" 
               style="text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 12px;">
                90 Ngày Qua
            </a>
        </div>

        <div style="font-size: 12.5px; color: #7A7265; background: #FAF8F5; padding: 6px 14px; border-radius: 6px; border: 1px solid #EFE8DD;">
            Đang xem: <strong>{{ $rangeData['label'] }}</strong> (từ {{ $rangeData['start']->format('d/m/Y') }} đến {{ $rangeData['end']->format('d/m/Y') }})
        </div>
    </form>
</div>

<!-- THANH ĐIỀU HƯỚNG 5 SUBTABS -->
<div style="display: flex; gap: 8px; border-bottom: 2px solid #EFE8DD; margin-bottom: 24px; overflow-x: auto;">
    <a href="{{ route('admin.analytics.index', ['tab' => 'revenue', 'range' => $range, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" 
       style="padding: 10px 18px; font-size: 13.5px; font-weight: 600; text-decoration: none; white-space: nowrap; color: {{ $activeTab === 'revenue' ? '#1F1D1A' : '#7A7265' }}; border-bottom: 2px solid {{ $activeTab === 'revenue' ? '#1F1D1A' : 'transparent' }}; margin-bottom: -2px;">
        Doanh Thu & Tăng Trưởng
    </a>

    <a href="{{ route('admin.analytics.index', ['tab' => 'funnel', 'range' => $range, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" 
       style="padding: 10px 18px; font-size: 13.5px; font-weight: 600; text-decoration: none; white-space: nowrap; color: {{ $activeTab === 'funnel' ? '#1F1D1A' : '#7A7265' }}; border-bottom: 2px solid {{ $activeTab === 'funnel' ? '#1F1D1A' : 'transparent' }}; margin-bottom: -2px;">
        Phễu Chuyển Đổi (Funnel)
    </a>

    <a href="{{ route('admin.analytics.index', ['tab' => 'templates', 'range' => $range, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" 
       style="padding: 10px 18px; font-size: 13.5px; font-weight: 600; text-decoration: none; white-space: nowrap; color: {{ $activeTab === 'templates' ? '#1F1D1A' : '#7A7265' }}; border-bottom: 2px solid {{ $activeTab === 'templates' ? '#1F1D1A' : 'transparent' }}; margin-bottom: -2px;">
        Hiệu Suất Mẫu Thiệp
    </a>

    <a href="{{ route('admin.analytics.index', ['tab' => 'rsvp', 'range' => $range, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" 
       style="padding: 10px 18px; font-size: 13.5px; font-weight: 600; text-decoration: none; white-space: nowrap; color: {{ $activeTab === 'rsvp' ? '#1F1D1A' : '#7A7265' }}; border-bottom: 2px solid {{ $activeTab === 'rsvp' ? '#1F1D1A' : 'transparent' }}; margin-bottom: -2px;">
        Khách Mời & RSVP
    </a>

    <a href="{{ route('admin.analytics.index', ['tab' => 'operations', 'range' => $range, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" 
       style="padding: 10px 18px; font-size: 13.5px; font-weight: 600; text-decoration: none; white-space: nowrap; color: {{ $activeTab === 'operations' ? '#1F1D1A' : '#7A7265' }}; border-bottom: 2px solid {{ $activeTab === 'operations' ? '#1F1D1A' : 'transparent' }}; margin-bottom: -2px;">
        Vận Hành & Hỗ Trợ
    </a>
</div>

{{-- ========================================================================= --}}
{{-- SUBTAB 1: DOANH THU & TĂNG TRƯỞNG                                         --}}
{{-- ========================================================================= --}}
@if($activeTab === 'revenue' && $revenueData)
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px; border-left: 4px solid #2D6A4F;">
            <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Doanh Thu Thực Thu (Net Revenue)</div>
            <div style="font-size: 24px; font-weight: 700; color: #2D6A4F;">
                {{ number_format($revenueData['net_revenue'], 0, ',', '.') }} đ
            </div>
            <div style="font-size: 12px; margin-top: 6px; color: {{ $revenueData['revenue_growth_pct'] >= 0 ? '#27AE60' : '#C0392B' }}; font-weight: 600;">
                {{ $revenueData['revenue_growth_pct'] >= 0 ? '▲ +' : '▼ ' }}{{ $revenueData['revenue_growth_pct'] }}% so với kỳ trước
            </div>
        </div>

        <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px; border-left: 4px solid #9C7A4A;">
            <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Tổng Tiền Gốc (Gross)</div>
            <div style="font-size: 24px; font-weight: 700; color: #1F1D1A;">
                {{ number_format($revenueData['gross_revenue'], 0, ',', '.') }} đ
            </div>
            <div style="font-size: 11.5px; color: #7A7265; margin-top: 6px;">
                Đã hoàn trả (Refund): {{ number_format($revenueData['refund_amount'] ?? 0, 0, ',', '.') }} đ
            </div>
        </div>

        <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px; border-left: 4px solid #0284C7;">
            <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Số Đơn Trả Phí (Paid Orders)</div>
            <div style="font-size: 24px; font-weight: 700; color: #0284C7;">
                {{ number_format($revenueData['orders_count'], 0, ',', '.') }}
            </div>
            <div style="font-size: 12px; margin-top: 6px; color: {{ $revenueData['orders_growth_pct'] >= 0 ? '#27AE60' : '#C0392B' }}; font-weight: 600;">
                {{ $revenueData['orders_growth_pct'] >= 0 ? '▲ +' : '▼ ' }}{{ $revenueData['orders_growth_pct'] }}% so với kỳ trước
            </div>
        </div>

        <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px; border-left: 4px solid #B8935D;">
            <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Giá Trị Đơn Trung Bình (AOV)</div>
            <div style="font-size: 24px; font-weight: 700; color: #1F1D1A;">
                {{ number_format($revenueData['aov'], 0, ',', '.') }} đ
            </div>
            <div style="font-size: 11.5px; color: #7A7265; margin-top: 6px;">
                Mỗi đơn hàng thành công
            </div>
        </div>
    </div>

    <!-- Biểu Đồ Xu Hướng Doanh Thu Hàng Ngày (Pure CSS Bar Chart) -->
    <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
        <h3 style="margin: 0 0 16px; font-size: 15px; font-weight: 600; color: #1F1D1A;">
            Biến Động Doanh Thu Theo Ngày Trong Kỳ
        </h3>
        @php
            $maxDayRevenue = max(1, collect($revenueData['daily_trend'])->max('revenue'));
        @endphp
        <div style="display: flex; align-items: flex-end; gap: 8px; height: 180px; padding-top: 20px; overflow-x: auto; border-bottom: 1px solid #EFE8DD;">
            @foreach($revenueData['daily_trend'] as $day)
                @php
                    $barHeight = $day['revenue'] > 0 ? max(8, round(($day['revenue'] / $maxDayRevenue) * 140)) : 4;
                @endphp
                <div style="flex: 1; min-width: 28px; display: flex; flex-direction: column; align-items: center; justify-content: flex-end;" title="{{ $day['date'] }}: {{ number_format($day['revenue'], 0, ',', '.') }} đ ({{ $day['orders_count'] }} đơn)">
                    <div style="width: 100%; max-width: 24px; height: {{ $barHeight }}px; background: {{ $day['revenue'] > 0 ? '#9C7A4A' : '#EFE8DD' }}; border-radius: 3px 3px 0 0; transition: height 0.3s ease;"></div>
                    <span style="font-size: 10px; color: #7A7265; margin-top: 6px; white-space: nowrap;">{{ $day['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- ========================================================================= --}}
{{-- SUBTAB 2: PHỄU CHUYỂN ĐỔI (FUNNEL)                                       --}}
{{-- ========================================================================= --}}
@if($activeTab === 'funnel' && $funnelData)
    <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h3 style="margin: 0 0 8px; font-size: 16px; font-weight: 600; color: #1F1D1A;">
            Hành Trình Chuyển Đổi Khách Hàng (Acquisition to Paid Funnel)
        </h3>
        <p style="font-size: 12.5px; color: #7A7265; margin-bottom: 24px;">
            Đo lường tỷ lệ giữ chân và drop-off qua 4 chặng: Xem Mẫu &rarr; Bắt Đầu Tạo &rarr; Xuất Bản Thiệp &rarr; Thanh Toán Gói Cước
        </p>

        <div style="display: flex; flex-direction: column; gap: 16px;">
            <!-- Chặng 1: Lượt Xem -->
            <div style="background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 8px; padding: 16px 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div>
                        <span style="font-size: 11.5px; font-weight: 700; color: #7A7265; text-transform: uppercase;">1. LƯỢT XEM MẪU & THIỆP CƯỚI (VIEWS)</span>
                        <div style="font-size: 20px; font-weight: 700; color: #1F1D1A;">{{ number_format($funnelData['total_views'], 0, ',', '.') }}</div>
                    </div>
                    <span class="mayhen_admin_badge mayhen_admin_badge--secondary">100% Khách Truy Cập</span>
                </div>
                <div style="width: 100%; height: 8px; background: #EFE8DD; border-radius: 4px; overflow: hidden;">
                    <div style="width: 100%; height: 100%; background: #7A7265;"></div>
                </div>
            </div>

            <!-- Chặng 2: Bắt Đầu Tạo -->
            <div style="background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 8px; padding: 16px 20px; margin-left: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div>
                        <span style="font-size: 11.5px; font-weight: 700; color: #0284C7; text-transform: uppercase;">2. BẮT ĐẦU TẠO DỰ ÁN CƯỚI (WEDDINGS CREATED)</span>
                        <div style="font-size: 20px; font-weight: 700; color: #0284C7;">{{ number_format($funnelData['total_created'], 0, ',', '.') }}</div>
                    </div>
                    <span class="mayhen_admin_badge mayhen_admin_badge--info">{{ $funnelData['view_to_create_pct'] }}% từ bước 1</span>
                </div>
                <div style="width: 100%; height: 8px; background: #EFE8DD; border-radius: 4px; overflow: hidden;">
                    <div style="width: {{ min(100, $funnelData['view_to_create_pct']) }}%; height: 100%; background: #0284C7;"></div>
                </div>
            </div>

            <!-- Chặng 3: Xuất Bản Thiệp -->
            <div style="background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 8px; padding: 16px 20px; margin-left: 40px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div>
                        <span style="font-size: 11.5px; font-weight: 700; color: #9C7A4A; text-transform: uppercase;">3. THIỆP ĐÃ XUẤT BẢN CÔNG KHAI (PUBLISHED)</span>
                        <div style="font-size: 20px; font-weight: 700; color: #9C7A4A;">{{ number_format($funnelData['total_published'], 0, ',', '.') }}</div>
                    </div>
                    <span class="mayhen_admin_badge mayhen_admin_badge--warning">{{ $funnelData['create_to_publish_pct'] }}% từ bước 2</span>
                </div>
                <div style="width: 100%; height: 8px; background: #EFE8DD; border-radius: 4px; overflow: hidden;">
                    <div style="width: {{ min(100, $funnelData['create_to_publish_pct']) }}%; height: 100%; background: #9C7A4A;"></div>
                </div>
            </div>

            <!-- Chặng 4: Đơn Hàng Thành Công -->
            <div style="background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 8px; padding: 16px 20px; margin-left: 60px; border-left: 4px solid #27AE60;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div>
                        <span style="font-size: 11.5px; font-weight: 700; color: #27AE60; text-transform: uppercase;">4. ĐƠN HÀNG TRẢ PHÍ HOÀN TẤT (PAID ORDERS)</span>
                        <div style="font-size: 20px; font-weight: 700; color: #27AE60;">{{ number_format($funnelData['total_paid'], 0, ',', '.') }}</div>
                    </div>
                    <span class="mayhen_admin_badge mayhen_admin_badge--success">{{ $funnelData['publish_to_paid_pct'] }}% từ bước 3</span>
                </div>
                <div style="width: 100%; height: 8px; background: #EFE8DD; border-radius: 4px; overflow: hidden;">
                    <div style="width: {{ min(100, $funnelData['publish_to_paid_pct']) }}%; height: 100%; background: #27AE60;"></div>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ========================================================================= --}}
{{-- SUBTAB 3: HIỆU SUẤT MẪU THIỆP                                             --}}
{{-- ========================================================================= --}}
@if($activeTab === 'templates' && $templateData)
    <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; overflow: hidden;">
        <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #FAF8F5; border-bottom: 1px solid #EFE8DD; color: #7A7265; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                    <th style="padding: 12px 16px;">Tên Mẫu & Slug</th>
                    <th style="padding: 12px 16px; width: 130px; text-align: center;">Phân Khúc</th>
                    <th style="padding: 12px 16px; width: 130px; text-align: center;">Lượt Xem</th>
                    <th style="padding: 12px 16px; width: 130px; text-align: center;">Số Lượng Tạo</th>
                    <th style="padding: 12px 16px; width: 140px; text-align: center;">Tỷ Lệ Dùng</th>
                    <th style="padding: 12px 16px; width: 160px; text-align: right;">Doanh Thu Mang Lại</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templateData as $tmpl)
                    <tr style="border-bottom: 1px solid #F5F1EB; font-size: 13.5px;">
                        <td style="padding: 14px 16px;">
                            <div style="font-weight: 600; color: #1F1D1A;">{{ $tmpl['name'] }}</div>
                            <code style="font-size: 11px; color: #7A7265; background: #FAF8F5; padding: 2px 6px; border-radius: 3px;">{{ $tmpl['slug'] }}</code>
                        </td>
                        <td style="padding: 14px 16px; text-align: center;">
                            <span class="mayhen_admin_badge {{ $tmpl['tier'] === 'vip' ? 'mayhen_admin_badge--primary' : 'mayhen_admin_badge--secondary' }}">
                                {{ strtoupper($tmpl['tier']) }}
                            </span>
                        </td>
                        <td style="padding: 14px 16px; text-align: center; font-weight: 600;">
                            {{ number_format($tmpl['views_count'], 0, ',', '.') }}
                        </td>
                        <td style="padding: 14px 16px; text-align: center; font-weight: 600; color: #0284C7;">
                            {{ number_format($tmpl['created_count'], 0, ',', '.') }}
                        </td>
                        <td style="padding: 14px 16px; text-align: center;">
                            <span style="font-weight: 600; color: {{ $tmpl['conversion_rate'] > 10 ? '#27AE60' : '#7A7265' }};">
                                {{ $tmpl['conversion_rate'] }}%
                            </span>
                        </td>
                        <td style="padding: 14px 16px; text-align: right; font-weight: 600; color: #2D6A4F;">
                            {{ number_format($tmpl['revenue'], 0, ',', '.') }} đ
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 40px; text-align: center; color: #8C8476;">
                            Chưa có dữ liệu hiệu suất mẫu thiệp trong khoảng thời gian này.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif

{{-- ========================================================================= --}}
{{-- SUBTAB 4: KHÁCH MỜI & RSVP                                                --}}
{{-- ========================================================================= --}}
@if($activeTab === 'rsvp' && $rsvpData)
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px; border-left: 4px solid #0284C7;">
            <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Tổng Số Khách Mời</div>
            <div style="font-size: 24px; font-weight: 700; color: #1F1D1A;">
                {{ number_format($rsvpData['total_guests'], 0, ',', '.') }}
            </div>
            <div style="font-size: 11.5px; color: #7A7265; margin-top: 6px;">
                Đã mở thiệp: {{ number_format($rsvpData['opened_count'], 0, ',', '.') }} ({{ $rsvpData['open_rate_pct'] }}%)
            </div>
        </div>

        <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px; border-left: 4px solid #27AE60;">
            <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Xác Nhận Tham Dự (Sẽ Đến)</div>
            <div style="font-size: 24px; font-weight: 700; color: #27AE60;">
                {{ number_format($rsvpData['attending_count'], 0, ',', '.') }}
            </div>
            <div style="font-size: 11.5px; color: #7A7265; margin-top: 6px;">
                Thực tế đi kèm (Headcount): <strong>{{ $rsvpData['total_headcount'] }} người</strong>
            </div>
        </div>

        <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px; border-left: 4px solid #C0392B;">
            <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Báo Bận (Không Tham Dự)</div>
            <div style="font-size: 24px; font-weight: 700; color: #C0392B;">
                {{ number_format($rsvpData['declined_count'], 0, ',', '.') }}
            </div>
            <div style="font-size: 11.5px; color: #7A7265; margin-top: 6px;">
                Gửi lời chúc phúc từ xa
            </div>
        </div>

        <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px; border-left: 4px solid #9C7A4A;">
            <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Tỷ Lệ Phản Hồi Chung</div>
            <div style="font-size: 24px; font-weight: 700; color: #9C7A4A;">
                {{ $rsvpData['response_rate_pct'] }}%
            </div>
            <div style="font-size: 11.5px; color: #7A7265; margin-top: 6px;">
                Còn lại {{ number_format($rsvpData['pending_count'], 0, ',', '.') }} khách đang chờ phản hồi
            </div>
        </div>
    </div>
@endif

{{-- ========================================================================= --}}
{{-- SUBTAB 5: VẬN HÀNH & HỖ TRỢ                                                --}}
{{-- ========================================================================= --}}
@if($activeTab === 'operations' && $operationsData)
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px; border-left: 4px solid #27AE60;">
            <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Tỷ Lệ Giải Quyết Ticket</div>
            <div style="font-size: 24px; font-weight: 700; color: #27AE60;">
                {{ $operationsData['ticket_resolution_rate'] }}%
            </div>
            <div style="font-size: 11.5px; color: #7A7265; margin-top: 6px;">
                Đã xử lý {{ $operationsData['resolved_tickets'] }} / {{ $operationsData['total_tickets'] }} phiếu hỗ trợ
            </div>
        </div>

        <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px; border-left: 4px solid {{ $operationsData['failed_jobs_count'] > 0 ? '#C0392B' : '#27AE60' }};">
            <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Sự Cố Tác Vụ Lỗi (Failed Jobs)</div>
            <div style="font-size: 24px; font-weight: 700; color: {{ $operationsData['failed_jobs_count'] > 0 ? '#C0392B' : '#27AE60' }};">
                {{ $operationsData['failed_jobs_count'] }}
            </div>
            <div style="font-size: 11.5px; color: #7A7265; margin-top: 6px;">
                Phát sinh trong khoảng thời gian đã chọn
            </div>
        </div>

        <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px; border-left: 4px solid #0284C7;">
            <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Lô Hoàn Tất Thành Công</div>
            <div style="font-size: 24px; font-weight: 700; color: #0284C7;">
                {{ $operationsData['completed_batches'] }}
            </div>
            <div style="font-size: 11.5px; color: #7A7265; margin-top: 6px;">
                Lô tác vụ xử lý ảnh & gửi thiệp
            </div>
        </div>
    </div>
@endif
@endsection
