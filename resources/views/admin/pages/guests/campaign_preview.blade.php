@extends('admin.layouts.master')

@section('title', 'Xem Trước Chiến Dịch Gửi Thiệp — Mây Hẹn')
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

            <!-- Right: Zero-Surprise Quality & Cost Checklist -->
            <div class="mayhen_admin_card">
                <div class="mayhen_admin_card_header">
                    <div class="mayhen_admin_card_title">
                        <span>🛡️ Cam Kết Chất Lượng & Chi Phí Chiến Dịch</span>
                    </div>
                </div>

                <div class="mayhen_admin_card_body">
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                            <span style="font-size: 1.1rem;">💰</span>
                            <div>
                                <strong style="font-size: 0.875rem; color: var(--mh-admin-charcoal);">Chi Phí Phát Sinh: 0đ (Miễn Phí 100%)</strong>
                                <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">
                                    Toàn bộ link thiệp mời và thông báo gửi qua Zalo/Mạng xã hội không tốn phí duy trì, tiết kiệm 95% so với in thiệp giấy truyền thống.
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                            <span style="font-size: 1.1rem;">⚡</span>
                            <div>
                                <strong style="font-size: 0.875rem; color: var(--mh-admin-charcoal);">Tỷ Lệ Mở Thiệp Dự Kiến: 85% - 92%</strong>
                                <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">
                                    Trải nghiệm mở phong bì lụa và nhạc cưới du dương kích thích sự tò mò và cảm xúc của quan khách ngay khi nhận link.
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                            <span style="font-size: 1.1rem;">📱</span>
                            <div>
                                <strong style="font-size: 0.875rem; color: var(--mh-admin-charcoal);">Tương Thích Thiết Bị 100%</strong>
                                <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">
                                    Hiển thị hoàn hảo trên iOS (iPhone/iPad), Android và Desktop không có độ trễ tải trang.
                                </div>
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
