@extends('admin.layouts.master')

@section('title', 'Xem Trước Chiến Dịch Gửi Thiệp')
@section('page_title', '📱 Zero-Surprise Campaign Launch (Xem Trước Chiến Dịch Gửi Thiệp)')

@section('content')
    <!-- Top Wedding Selector Bar -->
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 18px 20px; background: #FFFDF9; border: 1px solid var(--mh-admin-border);">
        <form action="{{ route('admin.guests.campaign_preview') }}" method="GET" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 280px;">
                <span style="font-size: 1.4rem;">🎯</span>
                <div>
                    <strong style="font-size: 0.9375rem; color: var(--mh-admin-charcoal);">Đám Cưới Cần Xem Trước Chiến Dịch:</strong>
                    <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">
                        Chọn đám cưới để kiểm tra phân đoạn quan khách và mô phỏng giao diện hiển thị khi chia sẻ link
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <select name="wedding_id" class="mayhen_admin_form_select" style="min-width: 260px;" onchange="this.form.submit()">
                    @foreach ($allWeddings as $w)
                        <option value="{{ $w->id }}" {{ $activeWedding && $activeWedding->id === $w->id ? 'selected' : '' }}>
                            {{ $w->groom_name }} &amp; {{ $w->bride_name }} ({{ $w->wedding_date ? $w->wedding_date->format('d/m/Y') : 'Chưa định' }})
                        </option>
                    @endforeach
                </select>

                <noscript>
                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary mayhen_admin_btn_sm">Chọn</button>
                </noscript>

                <a href="{{ route('admin.guests.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                    &larr; Về Guest CRM
                </a>
            </div>
        </form>
    </div>

    @if ($activeWedding)
        <!-- Metrics Overview Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                    Tổng Danh Bạ Khách Mời
                </div>
                <div style="font-size: 1.6rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                    {{ $metrics['total_guests'] }}
                </div>
                <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Khách dự kiến mời</div>
            </div>

            <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #2E7D32;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #2E7D32; margin-bottom: 6px;">
                    Đã Gửi Thiệp Mời
                </div>
                <div style="font-size: 1.6rem; font-weight: 700; color: #2E7D32;">
                    {{ $metrics['sent_count'] }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--mh-admin-muted);">({{ $metrics['send_rate'] }}%)</span>
                </div>
                <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Đã phát hành link thiệp</div>
            </div>

            <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #E65100;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #E65100; margin-bottom: 6px;">
                    Chưa Gửi Thiệp
                </div>
                <div style="font-size: 1.6rem; font-weight: 700; color: #E65100;">
                    {{ $metrics['unsent_count'] }}
                </div>
                <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Cần gửi link thông báo</div>
            </div>

            <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #0D47A1;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #0D47A1; margin-bottom: 6px;">
                    Đã Xác Nhận Tham Dự
                </div>
                <div style="font-size: 1.6rem; font-weight: 700; color: #0D47A1;">
                    {{ $metrics['attending_count'] }}
                </div>
                <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Chắc chắn có mặt</div>
            </div>
        </div>

        <!-- 2 Columns: Left OpenGraph Preview | Right Segmentation Details -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <!-- Left: OpenGraph & Zalo Message Card Simulator -->
            <div class="mayhen_admin_card">
                <div class="mayhen_admin_card_header">
                    <div class="mayhen_admin_card_title">
                        <span>💬 Mô Phỏng Hiển Thị Tin Nhắn Zalo / iMessage</span>
                    </div>
                </div>

                <div class="mayhen_admin_card_body">
                    <p style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 16px;">
                        Giao diện khách mời nhìn thấy khi cặp đôi dán đường link thiệp cưới vào khung chat Zalo, Messenger hoặc SMS:
                    </p>

                    <!-- Fake Chat Bubble -->
                    <div style="background: #EBF2FA; border-radius: 12px; padding: 14px; max-width: 360px; margin: 0 auto; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                        <div style="background: #fff; border-radius: 10px; overflow: hidden; border: 1px solid #D5E2F0;">
                            <!-- Thumbnail -->
                            <div style="height: 160px; background: #FAF6EE; display: flex; align-items: center; justify-content: center; position: relative;">
                                @if(!empty($activeWedding->cover_photo_url))
                                    <img src="{{ $activeWedding->cover_photo_url }}" alt="Cover" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div style="font-size: 2rem;">💍</div>
                                @endif
                                <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.6); color: #fff; font-size: 0.6875rem; padding: 2px 6px; border-radius: 4px;">
                                    mayhen.vn
                                </div>
                            </div>
                            <!-- Meta Content -->
                            <div style="padding: 12px;">
                                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--mh-admin-gold); font-weight: 700; letter-spacing: 0.05em;">
                                    THIỆP CƯỚI TRÂN QUÝ
                                </div>
                                <strong style="font-size: 0.95rem; color: #1F1D1A; display: block; margin: 4px 0;">
                                    {{ $activeWedding->groom_name }} &amp; {{ $activeWedding->bride_name }}
                                </strong>
                                <div style="font-size: 0.8125rem; color: #555; line-height: 1.4;">
                                    Trân trọng kính mời quý khách đến chung vui vào ngày {{ $activeWedding->wedding_date ? $activeWedding->wedding_date->format('d/m/Y') : 'ngày cưới' }} tại {{ $activeWedding->venue_name ?? 'Sảnh Tiệc' }}.
                                </div>
                            </div>
                        </div>
                        <div style="text-align: right; font-size: 0.6875rem; color: #888; margin-top: 4px;">
                            Đã gửi &bull; 10:30
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Real Measured Campaign Delivery & Performance (NXT-06) -->
            <div class="mayhen_admin_card">
                <div class="mayhen_admin_card_header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <div class="mayhen_admin_card_title">
                        <span>📊 Hiệu Suất Chiến Dịch Đo Lường Thực Tế (Measured Analytics)</span>
                    </div>
                    <span class="mayhen_admin_badge mayhen_admin_badge--success" style="font-size: 0.6875rem;">
                        ✓ Dữ Liệu Đo Thật 100%
                    </span>
                </div>

                <div class="mayhen_admin_card_body">
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        {{-- Metric 1: Tỷ lệ phát hành / gửi thiệp --}}
                        <div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 4px;">
                                <strong style="color: var(--mh-admin-charcoal);">Tỷ Lệ Gửi Thiệp Mời:</strong>
                                <span style="font-weight: 700; color: #2E7D32;">{{ $metrics['send_rate'] }}% ({{ $metrics['sent_count'] }}/{{ $metrics['total_guests'] }} khách)</span>
                            </div>
                            <div style="background: #EAE3D9; border-radius: 6px; height: 8px; overflow: hidden;">
                                <div style="background: #2E7D32; height: 100%; width: {{ min(100, $metrics['send_rate']) }}%;"></div>
                            </div>
                            <div style="font-size: 0.6875rem; color: var(--mh-admin-muted); margin-top: 2px;">
                                Tỷ lệ khách mời đã được gắn cờ phát hành link thiệp mời riêng tư.
                            </div>
                        </div>

                        {{-- Metric 2: Tỷ lệ phản hồi RSVP thực tế --}}
                        <div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 4px;">
                                <strong style="color: var(--mh-admin-charcoal);">Tỷ Lệ Phản Hồi RSVP Thực Tế:</strong>
                                <span style="font-weight: 700; color: #0D47A1;">{{ $metrics['response_rate_on_sent'] }}% ({{ $metrics['response_count'] }} phản hồi)</span>
                            </div>
                            <div style="background: #EAE3D9; border-radius: 6px; height: 8px; overflow: hidden;">
                                <div style="background: #0D47A1; height: 100%; width: {{ min(100, $metrics['response_rate_on_sent']) }}%;"></div>
                            </div>
                            <div style="font-size: 0.6875rem; color: var(--mh-admin-muted); margin-top: 2px;">
                                Tính trên số khách đã gửi thiệp (Không dùng tỷ lệ ước tính ảo).
                            </div>
                        </div>

                        {{-- Metric 3: Tỷ lệ xác nhận tham dự --}}
                        <div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 4px;">
                                <strong style="color: var(--mh-admin-charcoal);">Tỷ Lệ Khách Đi / Phản Hồi:</strong>
                                <span style="font-weight: 700; color: #D97706;">{{ $metrics['attending_rate_on_response'] }}% ({{ $metrics['attending_count'] }} khách tham dự)</span>
                            </div>
                            <div style="background: #EAE3D9; border-radius: 6px; height: 8px; overflow: hidden;">
                                <div style="background: #D97706; height: 100%; width: {{ min(100, $metrics['attending_rate_on_response']) }}%;"></div>
                            </div>
                            <div style="font-size: 0.6875rem; color: var(--mh-admin-muted); margin-top: 2px;">
                                Số khách chắc chắn có mặt tại sảnh tiệc dựa trên dữ liệu biểu mẫu RSVP đã ghi nhận.
                            </div>
                        </div>

                        {{-- Metric 4: Minh bạch chi phí nền tảng & viễn thông --}}
                        <div style="padding: 12px; background: #FAF8F5; border-radius: 8px; border: 1px solid var(--mh-admin-border);">
                            <div style="font-size: 0.8125rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-bottom: 4px;">
                                💳 Minh Bạch Về Chi Phí Phát Sinh
                            </div>
                            <div style="font-size: 0.75rem; color: var(--mh-admin-muted); line-height: 1.5;">
                                {{ $metrics['cost_notice'] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="mayhen_admin_card" style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 2.5rem; margin-bottom: 12px;">🕊️</div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-bottom: 6px;">
                Chưa Có Đám Cưới Nào
            </h3>
            <p style="font-size: 0.875rem; color: var(--mh-admin-muted); margin-bottom: 20px;">
                Hệ thống chưa ghi nhận dữ liệu đám cưới nào để xem trước chiến dịch.
            </p>
        </div>
    @endif
@endsection
