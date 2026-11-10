@extends('client.layouts.master')

@section('page_title', 'Báo Cáo Phân Tích & Hiệu Suất Tương Tác | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/account.css') }}">
@endpush

@section('content')
<div class="mayhen_account_container">
    <!-- Breadcrumbs -->
    <div class="mayhen_account_breadcrumbs">
        <a href="{{ route('home') }}">Trang chủ</a>
        <span class="mayhen_account_breadcrumbs_sep">/</span>
        <a href="{{ route('account.dashboard') }}">Tài khoản</a>
        <span class="mayhen_account_breadcrumbs_sep">/</span>
        <a href="{{ route('account.invitations') }}">Thiệp cưới</a>
        <span class="mayhen_account_breadcrumbs_sep">/</span>
        <span class="mayhen_account_breadcrumbs_current">Thống Kê & Phân Tích</span>
    </div>

    <!-- Header & Period Filter Toolbar -->
    <div class="mayhen_account_header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span style="font-size: 24px;">📊</span>
                <h1 class="mayhen_account_title" style="margin: 0;">Báo Cáo Phân Tích & Hiệu Suất Tương Tác</h1>
            </div>
            <p class="mayhen_account_subtitle" style="margin: 0; color: #736B63;">
                Thiệp cưới: <strong>{{ $wedding->groom_name ?? 'Chú Rể' }} & {{ $wedding->bride_name ?? 'Cô Dâu' }}</strong> ({{ $invitation->title }}) &bull;
                Đo lường 100% dữ liệu thực tế, bảo vệ quyền riêng tư theo Luật 91/2025/QH15.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <!-- Bộ lọc thời gian -->
            <div style="display: inline-flex; background: #F0EDE8; padding: 4px; border-radius: 8px; border: 1px solid #E5DCCD;">
                <a href="{{ route('account.invitations.analytics', ['id' => $invitation->id, 'days' => 7]) }}" 
                   style="padding: 6px 12px; font-size: 13px; font-weight: 500; border-radius: 6px; text-decoration: none; color: {{ $days === 7 ? '#1F1D1A' : '#736B63' }}; background: {{ $days === 7 ? '#FFFFFF' : 'transparent' }}; box-shadow: {{ $days === 7 ? '0 1px 3px rgba(0,0,0,0.08)' : 'none' }};">
                    7 ngày
                </a>
                <a href="{{ route('account.invitations.analytics', ['id' => $invitation->id, 'days' => 14]) }}" 
                   style="padding: 6px 12px; font-size: 13px; font-weight: 500; border-radius: 6px; text-decoration: none; color: {{ $days === 14 ? '#1F1D1A' : '#736B63' }}; background: {{ $days === 14 ? '#FFFFFF' : 'transparent' }}; box-shadow: {{ $days === 14 ? '0 1px 3px rgba(0,0,0,0.08)' : 'none' }};">
                    14 ngày
                </a>
                <a href="{{ route('account.invitations.analytics', ['id' => $invitation->id, 'days' => 30]) }}" 
                   style="padding: 6px 12px; font-size: 13px; font-weight: 500; border-radius: 6px; text-decoration: none; color: {{ $days === 30 ? '#1F1D1A' : '#736B63' }}; background: {{ $days === 30 ? '#FFFFFF' : 'transparent' }}; box-shadow: {{ $days === 30 ? '0 1px 3px rgba(0,0,0,0.08)' : 'none' }};">
                    30 ngày
                </a>
                <a href="{{ route('account.invitations.analytics', ['id' => $invitation->id, 'days' => 90]) }}" 
                   style="padding: 6px 12px; font-size: 13px; font-weight: 500; border-radius: 6px; text-decoration: none; color: {{ $days === 90 ? '#1F1D1A' : '#736B63' }}; background: {{ $days === 90 ? '#FFFFFF' : 'transparent' }}; box-shadow: {{ $days === 90 ? '0 1px 3px rgba(0,0,0,0.08)' : 'none' }};">
                    90 ngày
                </a>
            </div>

            <!-- Nút xuất CSV -->
            <a href="{{ route('account.invitations.analytics.export', ['id' => $invitation->id, 'days' => $days]) }}" class="mayhen_account_btn mayhen_account_btn_outline" title="Tải xuống tệp CSV phân tích">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Xuất CSV</span>
            </a>

            <!-- Xem thiệp công khai -->
            <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_primary" title="Mở trang thiệp cưới ngoài web">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
                <span>Xem Thiệp</span>
            </a>
        </div>
    </div>

    <!-- 6 KPI Metric Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px;">
        <!-- KPI 1: Tổng lượt xem -->
        <div class="mayhen_account_card" style="padding: 18px 20px; border-left: 4px solid #C5A880; background: #FFFFFF;">
            <div style="font-size: 13px; color: #8C827A; font-weight: 500; margin-bottom: 6px;">Tổng Lượt Xem Thiệp</div>
            <div style="font-size: 28px; font-weight: 700; color: #1F1D1A; line-height: 1.2;">
                {{ number_format($stats['summary']['total_views']) }}
            </div>
            <div style="font-size: 12px; color: #9C7A4A; margin-top: 6px; font-weight: 500;">
                {{ number_format($stats['summary']['period_views']) }} lượt trong {{ $days }} ngày qua
            </div>
        </div>

        <!-- KPI 2: Khách riêng biệt -->
        <div class="mayhen_account_card" style="padding: 18px 20px; border-left: 4px solid #748E8A; background: #FFFFFF;">
            <div style="font-size: 13px; color: #8C827A; font-weight: 500; margin-bottom: 6px;">Người Xem Độc Lập</div>
            <div style="font-size: 28px; font-weight: 700; color: #1F1D1A; line-height: 1.2;">
                {{ number_format($stats['summary']['unique_visitors']) }}
            </div>
            <div style="font-size: 12px; color: #748E8A; margin-top: 6px; font-weight: 500;">
                Mã băm ẩn danh (Unique Visitors)
            </div>
        </div>

        <!-- KPI 3: Khách đã mở thiệp cá nhân -->
        <div class="mayhen_account_card" style="padding: 18px 20px; border-left: 4px solid #2E7D32; background: #FFFFFF;">
            <div style="font-size: 13px; color: #8C827A; font-weight: 500; margin-bottom: 6px;">Khách Đã Mở Thiệp</div>
            <div style="font-size: 28px; font-weight: 700; color: #2E7D32; line-height: 1.2;">
                {{ number_format($stats['summary']['opened_guests']) }} <span style="font-size: 16px; color: #8C827A; font-weight: 400;">/ {{ number_format($stats['summary']['sent_guests']) }}</span>
            </div>
            <div style="font-size: 12px; color: #2E7D32; margin-top: 6px; font-weight: 500;">
                Tỷ lệ mở đạt {{ $stats['summary']['open_rate'] }}%
            </div>
        </div>

        <!-- KPI 4: Phản hồi tham dự RSVP -->
        <div class="mayhen_account_card" style="padding: 18px 20px; border-left: 4px solid #1565C0; background: #FFFFFF;">
            <div style="font-size: 13px; color: #8C827A; font-weight: 500; margin-bottom: 6px;">Phản Hồi Tham Dự RSVP</div>
            <div style="font-size: 28px; font-weight: 700; color: #1565C0; line-height: 1.2;">
                {{ number_format($stats['summary']['total_responses']) }}
            </div>
            <div style="font-size: 12px; color: #1565C0; margin-top: 6px; font-weight: 500;">
                Tỷ lệ chuyển đổi {{ $stats['summary']['rsvp_conversion_rate'] }}%
            </div>
        </div>

        <!-- KPI 5: Khách sẽ đến dự tiệc -->
        <div class="mayhen_account_card" style="padding: 18px 20px; border-left: 4px solid #E65100; background: #FFFFFF;">
            <div style="font-size: 13px; color: #8C827A; font-weight: 500; margin-bottom: 6px;">Suất Khách Chắc Chắn Đến</div>
            <div style="font-size: 28px; font-weight: 700; color: #E65100; line-height: 1.2;">
                {{ number_format($stats['summary']['attending_headcount']) }}
            </div>
            <div style="font-size: 12px; color: #E65100; margin-top: 6px; font-weight: 500;">
                {{ number_format($stats['summary']['attending_responses']) }} phản hồi xác nhận
            </div>
        </div>

        <!-- KPI 6: Lời chúc phúc -->
        <div class="mayhen_account_card" style="padding: 18px 20px; border-left: 4px solid #D9B4A7; background: #FFFFFF;">
            <div style="font-size: 13px; color: #8C827A; font-weight: 500; margin-bottom: 6px;">Sổ Lưu Bút & Chúc Phúc</div>
            <div style="font-size: 28px; font-weight: 700; color: #1F1D1A; line-height: 1.2;">
                {{ number_format($stats['summary']['total_wishes']) }}
            </div>
            <div style="font-size: 12px; color: #8C827A; margin-top: 6px; font-weight: 500;">
                {{ number_format($stats['summary']['approved_wishes']) }} lời chúc hiển thị
            </div>
        </div>
    </div>

    <!-- Chart: Lượng xem 7 ngày gần nhất (Pure SVG & Native CSS) -->
    <div class="mayhen_account_card" style="padding: 24px; margin-bottom: 28px; background: #FFFFFF;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h2 style="font-size: 18px; font-weight: 700; color: #1F1D1A; margin: 0 0 4px 0;">
                    📈 Biểu Đồ Lượng Truy Cập 7 Ngày Gần Nhất
                </h2>
                <p style="font-size: 13px; color: #8C827A; margin: 0;">
                    So sánh giữa tổng lượt xem (Views) và người xem độc lập (Unique Visitors).
                </p>
            </div>
            <div style="display: flex; align-items: center; gap: 16px; font-size: 13px;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: #C5A880; border-radius: 3px;"></span>
                    <span style="color: #4A453E;">Lượt xem</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: #748E8A; border-radius: 3px;"></span>
                    <span style="color: #4A453E;">Người xem độc lập</span>
                </div>
            </div>
        </div>

        <!-- Pure CSS/SVG Bar Chart Component -->
        <div style="display: flex; align-items: flex-end; justify-content: space-between; height: 200px; padding-top: 20px; border-bottom: 1px solid #E5DCCD; gap: 8px;">
            @php $maxVal = $stats['max_timeline_value']; @endphp
            @foreach($stats['timeline'] as $item)
                @php
                    $viewsHeight = $maxVal > 0 ? max(4, round(($item['views'] / $maxVal) * 160)) : 4;
                    $uniqueHeight = $maxVal > 0 ? max(4, round(($item['unique'] / $maxVal) * 160)) : 4;
                @endphp
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; height: 100%; justify-content: flex-end;">
                    <div style="font-size: 11px; font-weight: 600; color: #9C7A4A; min-height: 16px;">
                        {{ $item['views'] > 0 ? $item['views'] : '' }}
                    </div>
                    <div style="display: flex; align-items: flex-end; gap: 4px; width: 100%; justify-content: center;">
                        <!-- Bar 1: Views -->
                        <div style="width: 14px; max-width: 40%; height: {{ $viewsHeight }}px; background: #C5A880; border-radius: 4px 4px 0 0;" title="{{ $item['date'] }}: {{ $item['views'] }} lượt xem"></div>
                        <!-- Bar 2: Unique -->
                        <div style="width: 14px; max-width: 40%; height: {{ $uniqueHeight }}px; background: #748E8A; border-radius: 4px 4px 0 0;" title="{{ $item['date'] }}: {{ $item['unique'] }} người xem độc lập"></div>
                    </div>
                    <div style="font-size: 12px; color: #736B63; margin-top: 8px; font-weight: 500;">
                        {{ $item['date'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Phễu Chuyển Đổi Tương Tác Cưới (Wedding Interaction Funnel) -->
    <div class="mayhen_account_card" style="padding: 24px; margin-bottom: 28px; background: #FFFFFF;">
        <div style="margin-bottom: 20px;">
            <h2 style="font-size: 18px; font-weight: 700; color: #1F1D1A; margin: 0 0 4px 0;">
                🎯 Phễu Chuyển Đổi Quan Khách (Wedding Conversion Funnel)
            </h2>
            <p style="font-size: 13px; color: #8C827A; margin: 0;">
                Đo lường từng chặng hành trình từ khi gửi đường link đến lúc khách để lại lời chúc phúc trên thiệp cưới.
            </p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 16px;">
            @foreach($stats['funnel'] as $stage)
                <div style="background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 8px; padding: 14px 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; background: #C5A880; color: #FFFFFF; font-weight: 700; font-size: 12px; border-radius: 50%;">
                                {{ $stage['step'] }}
                            </span>
                            <span style="font-size: 14px; font-weight: 600; color: #1F1D1A;">
                                {{ $stage['name'] }}
                            </span>
                        </div>
                        <div style="font-size: 14px; font-weight: 700; color: #1F1D1A;">
                            {{ number_format($stage['count']) }} {{ $stage['unit'] }}
                            <span style="font-size: 12px; font-weight: 500; color: #9C7A4A; margin-left: 6px;">
                                ({{ $stage['rate'] }}%)
                            </span>
                        </div>
                    </div>

                    <!-- Progress bar -->
                    <div style="width: 100%; height: 8px; background: #E5DCCD; border-radius: 4px; overflow: hidden;">
                        <div style="width: {{ min(100, max(2, $stage['rate'])) }}%; height: 100%; background: linear-gradient(90deg, #C5A880, #9C7A4A); border-radius: 4px;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Grid 2 Cột: Phân Bố Thiết Bị & Ứng Dụng Mở Thiệp -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 28px;">
        <!-- Cột 1: Loại thiết bị -->
        <div class="mayhen_account_card" style="padding: 24px; background: #FFFFFF;">
            <h3 style="font-size: 16px; font-weight: 700; color: #1F1D1A; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                <span>📱</span> Thiết Bị Truy Cập
            </h3>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                <!-- Mobile -->
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px;">
                        <span style="font-weight: 500; color: #1F1D1A;">Điện thoại di động (Smartphone)</span>
                        <span style="font-weight: 600; color: #9C7A4A;">{{ $stats['devices']['mobile']['count'] }} ({{ $stats['devices']['mobile']['pct'] }}%)</span>
                    </div>
                    <div style="width: 100%; height: 6px; background: #F0EDE8; border-radius: 3px; overflow: hidden;">
                        <div style="width: {{ $stats['devices']['mobile']['pct'] }}%; height: 100%; background: #2E7D32; border-radius: 3px;"></div>
                    </div>
                </div>

                <!-- Desktop -->
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px;">
                        <span style="font-weight: 500; color: #1F1D1A;">Máy tính bàn & Laptop</span>
                        <span style="font-weight: 600; color: #9C7A4A;">{{ $stats['devices']['desktop']['count'] }} ({{ $stats['devices']['desktop']['pct'] }}%)</span>
                    </div>
                    <div style="width: 100%; height: 6px; background: #F0EDE8; border-radius: 3px; overflow: hidden;">
                        <div style="width: {{ $stats['devices']['desktop']['pct'] }}%; height: 100%; background: #1565C0; border-radius: 3px;"></div>
                    </div>
                </div>

                <!-- Tablet -->
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px;">
                        <span style="font-weight: 500; color: #1F1D1A;">Máy tính bảng (Tablet / iPad)</span>
                        <span style="font-weight: 600; color: #9C7A4A;">{{ $stats['devices']['tablet']['count'] }} ({{ $stats['devices']['tablet']['pct'] }}%)</span>
                    </div>
                    <div style="width: 100%; height: 6px; background: #F0EDE8; border-radius: 3px; overflow: hidden;">
                        <div style="width: {{ $stats['devices']['tablet']['pct'] }}%; height: 100%; background: #C5A880; border-radius: 3px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột 2: Trình duyệt & Kênh In-App -->
        <div class="mayhen_account_card" style="padding: 24px; background: #FFFFFF;">
            <h3 style="font-size: 16px; font-weight: 700; color: #1F1D1A; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                <span>🌐</span> Ứng Dụng Mở Thiệp (In-App Browsers)
            </h3>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse($stats['browsers'] as $browser => $count)
                    @php
                        $browserName = match($browser) {
                            'zalo' => 'Ứng dụng Zalo (In-App)',
                            'facebook' => 'Ứng dụng Facebook / Messenger',
                            'safari' => 'Apple Safari (iOS/macOS)',
                            'chrome' => 'Google Chrome',
                            'edge' => 'Microsoft Edge',
                            'firefox' => 'Mozilla Firefox',
                            default => 'Trình duyệt khác',
                        };
                        $totalB = array_sum($stats['browsers']);
                        $bPct = $totalB > 0 ? round(($count / $totalB) * 100, 1) : 0;
                    @endphp
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px;">
                            <span style="font-weight: 500; color: #1F1D1A;">{{ $browserName }}</span>
                            <span style="font-weight: 600; color: #9C7A4A;">{{ $count }} lượt ({{ $bPct }}%)</span>
                        </div>
                        <div style="width: 100%; height: 6px; background: #F0EDE8; border-radius: 3px; overflow: hidden;">
                            <div style="width: {{ $bPct }}%; height: 100%; background: #748E8A; border-radius: 3px;"></div>
                        </div>
                    </div>
                @empty
                    <p style="font-size: 13px; color: #8C827A; margin: 0;">Chưa ghi nhận lượt mở thiệp nào trong khoảng thời gian này.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quyền Riêng Tư & An Toàn Dữ Liệu -->
    <div style="background: #F8F6F0; border: 1px solid #E5DCCD; border-radius: 8px; padding: 16px 20px; display: flex; align-items: flex-start; gap: 12px;">
        <span style="font-size: 20px; line-height: 1;">🛡️</span>
        <div style="font-size: 13px; color: #736B63; line-height: 1.5;">
            <strong style="color: #1F1D1A;">Cam kết bảo vệ quyền riêng tư theo Luật 91/2025/QH15:</strong>
            Mây Hẹn áp dụng chuẩn bảo mật không lưu IP thô của khách mời. Mọi lượt xem được xác định thông qua mã băm SHA-256 ẩn danh một chiều tự động xoay vòng theo ngày. Dữ liệu phân tích hoàn toàn phục vụ mục đích tổ chức đám cưới của gia đình, tuyệt đối không chia sẻ cho bên thứ ba hoặc mạng lưới quảng cáo.
        </div>
    </div>
</div>
@endsection

