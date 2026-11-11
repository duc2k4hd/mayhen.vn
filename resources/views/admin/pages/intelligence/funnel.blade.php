@extends('admin.layouts.master')

@section('title', 'Phễu Chuyển Đổi Vận Hành Thực Tế (Real Funnel Analytics)')
@section('page_title', '📈 Phễu Chuyển Đổi Vận Hành Thực Tế (NXT-06)')

@section('content')
    {{-- Header & Time Range Filter --}}
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 18px 20px; background: #FFFDF9; border: 1px solid var(--mh-admin-border);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="font-size: 1rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                    Phễu Chuyển Đổi Khách Hàng Toàn Hệ Thống (Real Funnel Engine)
                </div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-top: 2px;">
                    100% dữ liệu đo lường trực tiếp từ cơ sở dữ liệu thật (Không sử dụng bất kỳ tỷ lệ ước tính ảo hay hardcode).
                </div>
            </div>

            <div style="display: flex; gap: 8px;">
                <a href="{{ route('admin.intelligence.funnel', ['days' => 0]) }}" class="mayhen_admin_btn mayhen_admin_btn_sm {{ $currentDays === 0 ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn_outline' }}">
                    Toàn thời gian
                </a>
                <a href="{{ route('admin.intelligence.funnel', ['days' => 30]) }}" class="mayhen_admin_btn mayhen_admin_btn_sm {{ $currentDays === 30 ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn_outline' }}">
                    30 ngày qua
                </a>
                <a href="{{ route('admin.intelligence.funnel', ['days' => 60]) }}" class="mayhen_admin_btn mayhen_admin_btn_sm {{ $currentDays === 60 ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn_outline' }}">
                    60 ngày qua
                </a>
                <a href="{{ route('admin.intelligence.funnel', ['days' => 90]) }}" class="mayhen_admin_btn mayhen_admin_btn_sm {{ $currentDays === 90 ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn_outline' }}">
                    90 ngày qua
                </a>
            </div>
        </div>
    </div>

    {{-- Funnel Stages Visual Stack --}}
    <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 32px;">
        @php
            $stages = $funnel['stages'];
            $maxCount = max(1, $stages['views']['count'], $stages['invitations']['count']);
        @endphp

        {{-- Stage 1: Views --}}
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 20px; border-left: 5px solid #6B7280;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div>
                    <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #6B7280;">Chặng 1 &bull; Tiếp Cận</span>
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 2px 0 0;">
                        {{ $stages['views']['label'] }}
                    </h3>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.8rem; font-weight: 800; color: var(--mh-admin-charcoal); line-height: 1;">
                        {{ number_format($stages['views']['count'], 0, ',', '.') }}
                    </div>
                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">{{ $stages['views']['unit'] }}</div>
                </div>
            </div>
            <div style="background: #F3F4F6; height: 12px; border-radius: 6px; overflow: hidden;">
                <div style="background: #6B7280; height: 100%; width: 100%;"></div>
            </div>
            <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 6px;">
                Cửa ngõ đầu phễu: Khách vãng lai và các cặp đôi ghé thăm, xem các bản xem thử mẫu thiệp cưới.
            </div>
        </div>

        {{-- Stage 2: Created Invitations --}}
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 20px; border-left: 5px solid #3B82F6;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div>
                    <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #3B82F6;">Chặng 2 &bull; Kích Hoạt Tạo Thử</span>
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 2px 0 0;">
                        {{ $stages['invitations']['label'] }}
                    </h3>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.8rem; font-weight: 800; color: #3B82F6; line-height: 1;">
                        {{ number_format($stages['invitations']['count'], 0, ',', '.') }}
                    </div>
                    <div style="font-size: 0.75rem; color: #3B82F6; font-weight: 600;">
                        {{ $stages['invitations']['conversion_rate_from_prev'] }}% chuyển đổi từ lượt xem
                    </div>
                </div>
            </div>
            @php
                $w2 = $stages['views']['count'] > 0 ? min(100, round(($stages['invitations']['count'] / $stages['views']['count']) * 100, 1)) : ($stages['invitations']['count'] > 0 ? 100 : 0);
            @endphp
            <div style="background: #F3F4F6; height: 12px; border-radius: 6px; overflow: hidden;">
                <div style="background: #3B82F6; height: 100%; width: {{ $w2 }}%;"></div>
            </div>
            <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 6px;">
                Cặp đôi nhập thông tin cô dâu chú rể và tạo thiệp cưới trong Studio.
            </div>
        </div>

        {{-- Stage 3: Paid Orders --}}
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 20px; border-left: 5px solid #10B981;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div>
                    <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #10B981;">Chặng 3 &bull; Trả Phí Thương Mại</span>
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 2px 0 0;">
                        {{ $stages['paid_orders']['label'] }}
                    </h3>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.8rem; font-weight: 800; color: #10B981; line-height: 1;">
                        {{ number_format($stages['paid_orders']['count'], 0, ',', '.') }}
                    </div>
                    <div style="font-size: 0.75rem; color: #10B981; font-weight: 600;">
                        {{ $stages['paid_orders']['conversion_rate_from_prev'] }}% chuyển đổi từ thiệp tạo
                    </div>
                </div>
            </div>
            @php
                $w3 = $stages['invitations']['count'] > 0 ? min(100, round(($stages['paid_orders']['count'] / $stages['invitations']['count']) * 100, 1)) : 0;
            @endphp
            <div style="background: #F3F4F6; height: 12px; border-radius: 6px; overflow: hidden;">
                <div style="background: #10B981; height: 100%; width: {{ $w3 }}%;"></div>
            </div>
            <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 6px;">
                Đơn hàng thanh toán thành công qua VietQR/SePay đã được đối soát sổ cái.
            </div>
        </div>

        {{-- Stage 4: Sent Guests --}}
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 20px; border-left: 5px solid #F59E0B;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div>
                    <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #F59E0B;">Chặng 4 &bull; Phát Hành Thiệp Mời</span>
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 2px 0 0;">
                        {{ $stages['sent_guests']['label'] }}
                    </h3>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.8rem; font-weight: 800; color: #F59E0B; line-height: 1;">
                        {{ number_format($stages['sent_guests']['count'], 0, ',', '.') }} / {{ number_format($stages['sent_guests']['total_guests'], 0, ',', '.') }}
                    </div>
                    <div style="font-size: 0.75rem; color: #F59E0B; font-weight: 600;">
                        {{ $stages['sent_guests']['conversion_rate_from_prev'] }}% tỷ lệ phát hành trên danh bạ
                    </div>
                </div>
            </div>
            @php
                $w4 = $stages['sent_guests']['conversion_rate_from_prev'];
            @endphp
            <div style="background: #F3F4F6; height: 12px; border-radius: 6px; overflow: hidden;">
                <div style="background: #F59E0B; height: 100%; width: {{ min(100, $w4) }}%;"></div>
            </div>
            <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 6px;">
                Cặp đôi sao chép và gửi link mời cá nhân hóa cho từng khách mời trong danh bạ.
            </div>
        </div>

        {{-- Stage 5: RSVP Responses --}}
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 20px; border-left: 5px solid #8B5CF6;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div>
                    <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #8B5CF6;">Chặng 5 &bull; Tương Tác & RSVP</span>
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 2px 0 0;">
                        {{ $stages['rsvp_responses']['label'] }}
                    </h3>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.8rem; font-weight: 800; color: #8B5CF6; line-height: 1;">
                        {{ number_format($stages['rsvp_responses']['count'], 0, ',', '.') }}
                    </div>
                    <div style="font-size: 0.75rem; color: #8B5CF6; font-weight: 600;">
                        {{ $stages['rsvp_responses']['conversion_rate_from_prev'] }}% phản hồi / thiệp đã gửi &bull; {{ $stages['rsvp_responses']['attending_rate'] }}% xác nhận đi
                    </div>
                </div>
            </div>
            @php
                $w5 = $stages['rsvp_responses']['conversion_rate_from_prev'];
            @endphp
            <div style="background: #F3F4F6; height: 12px; border-radius: 6px; overflow: hidden;">
                <div style="background: #8B5CF6; height: 100%; width: {{ min(100, $w5) }}%;"></div>
            </div>
            <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 6px;">
                Quan khách mở thiệp, xác nhận tham dự ({{ $stages['rsvp_responses']['attending_count'] }} khách chắc chắn có mặt).
            </div>
        </div>
    </div>

    {{-- Transparency Card --}}
    <div class="mayhen_admin_card" style="background: #FAF8F5; border: 1px solid var(--mh-admin-border);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: #2E7D32; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                ✓
            </div>
            <div>
                <strong style="font-size: 0.875rem; color: var(--mh-admin-charcoal);">Cam Kết Minh Bạch Vận Hành (Anti-Fake Metrics Standard)</strong>
                <p style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin: 2px 0 0; line-height: 1.5;">
                    Toàn bộ chỉ số trên được truy vấn trực tiếp từ cơ sở dữ liệu thật của Mây Hẹn (MySQL). Hệ thống kiên quyết không dùng số liệu ước lượng, không hardcode tỷ lệ mở giả định, bảo đảm mọi quyết định kinh doanh và hỗ trợ cặp đôi đều dựa trên sự thật khách quan.
                </p>
            </div>
        </div>
    </div>
@endsection

