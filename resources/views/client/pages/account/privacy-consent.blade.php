@extends('client.layouts.master')

@section('title', 'Trung Tâm Quyền Riêng Tư & Bảo Mật Dữ Liệu | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/privacy-consent.css') }}">
@endpush

@section('content')
<div class="mayhen_privacy_wrap">
    <div class="mayhen_privacy_container">
        {{-- Breadcrumbs --}}
        <div class="mayhen_privacy_breadcrumbs">
            <a href="{{ route('account.dashboard') }}">Tài khoản</a>
            <span>/</span>
            <a href="{{ route('account.invitations') }}">Thiệp cưới</a>
            <span>/</span>
            <a href="{{ route('account.invitations.settings', $invitation->id) }}">Cài đặt</a>
            <span>/</span>
            <span>Quyền Riêng Tư &amp; Bảo Mật</span>
        </div>

        @if(session('success'))
            <div style="background: #E8F5E9; border: 1px solid #C8E6C9; color: #2E7D32; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                {{ session('success') }}
            </div>
        @endif

        {{-- Header --}}
        <header class="mayhen_privacy_header">
            <span class="privacy_badge">PRIVACY &amp; CONSENT CENTER</span>
            <h1 class="privacy_title">Trung Tâm Minh Bạch Quyền Riêng Tư</h1>
            <p class="privacy_subtitle">
                Hôn lễ: <strong>{{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}</strong> &bull;
                Kiểm soát hiển thị số tài khoản mừng cưới, bảo mật số điện thoại khách mời và tuân thủ Nghị định 13/2023/NĐ-CP &amp; Luật 91/2025/QH15.
            </p>
        </header>

        {{-- 1. Privacy Compliance Score Card --}}
        <div class="mayhen_privacy_score_card">
            <div class="score_card_top">
                <div>
                    <h2 style="font-size: 1.15rem; font-weight: 700; color: #1F1D1A; margin: 0 0 4px;">
                        Điểm Đánh Giá Bảo Vệ Dữ Liệu Cá Nhân
                    </h2>
                    <span style="font-size: 0.85rem; color: #6E685F;">
                        Tự động rà soát các rủi ro lộ số tài khoản và thông tin liên hệ của khách mời
                    </span>
                </div>
                <div class="score_badge_group">
                    <span class="score_pill score_pill--{{ $report['status'] }}">
                        {{ $report['privacy_score'] }}/100
                    </span>
                    <strong style="font-size: 0.95rem; color: #1F1D1A;">{{ $report['status_label'] }}</strong>
                </div>
            </div>

            <div class="score_stats_grid">
                <div class="score_stat_box">
                    <span class="score_stat_label">TỔNG KHÁCH MỜI</span>
                    <div class="score_stat_num">{{ number_format($report['statistics']['total_guests']) }}</div>
                </div>

                <div class="score_stat_box">
                    <span class="score_stat_label">KHÁCH CÓ SỐ ĐIỆN THOẠI</span>
                    <div class="score_stat_num" style="color: #F57F17;">{{ number_format($report['statistics']['guests_with_phone']) }}</div>
                </div>

                <div class="score_stat_box">
                    <span class="score_stat_label">LƯỢT PHẢN HỒI RSVP</span>
                    <div class="score_stat_num" style="color: #2E7D32;">{{ number_format($report['statistics']['total_responses']) }}</div>
                </div>

                <div class="score_stat_box">
                    <span class="score_stat_label">LỜI CHÚC CÔNG KHAI</span>
                    <div class="score_stat_num" style="color: #512DA8;">{{ number_format($report['statistics']['public_wishes_count']) }}</div>
                </div>
            </div>
        </div>

        {{-- 2. Settings Form Card --}}
        <div class="mayhen_privacy_form_card">
            <h2 class="form_card_title">Cấu Hình Quyền Riêng Tư &amp; Hiển Thị Thiệp Cưới</h2>

            <form action="{{ route('privacy.update', $invitation->id) }}" method="POST">
                @csrf

                {{-- A. Hiển thị thông tin mừng cưới --}}
                <div class="privacy_setting_item">
                    <div class="setting_item_head">
                        <div>
                            <h3 class="setting_item_title">1. Hiển Thị Số Tài Khoản &amp; QR Mừng Cưới</h3>
                            <p class="setting_item_desc">Kiểm soát ai được phép nhìn thấy số tài khoản ngân hàng của cô dâu chú rể để tránh kẻ gian lợi dụng.</p>
                        </div>
                    </div>
                    <div class="privacy_radio_group">
                        <label class="privacy_radio_label">
                            <input type="radio" name="bank_visibility" value="named_guests_only" {{ $settings['bank_visibility'] === 'named_guests_only' ? 'checked' : '' }}>
                            <span><strong>Chỉ khách có thiệp định danh cá nhân (Khuyên dùng)</strong> &mdash; Khách vãng lai xem link chung sẽ bị che 4 số cuối.</span>
                        </label>
                        <label class="privacy_radio_label">
                            <input type="radio" name="bank_visibility" value="everyone" {{ $settings['bank_visibility'] === 'everyone' ? 'checked' : '' }}>
                            <span><strong>Công khai cho tất cả mọi người</strong> &mdash; Ai truy cập đường link thiệp cũng nhìn thấy đầy đủ số tài khoản.</span>
                        </label>
                        <label class="privacy_radio_label">
                            <input type="radio" name="bank_visibility" value="hidden" {{ $settings['bank_visibility'] === 'hidden' ? 'checked' : '' }}>
                            <span><strong>Tắt hoàn toàn mừng cưới trực tuyến</strong> &mdash; Ẩn mục chuyển khoản ngân hàng trên thiệp.</span>
                        </label>
                    </div>
                </div>

                {{-- B. Quyền riêng tư Sổ Lời Chúc --}}
                <div class="privacy_setting_item">
                    <div class="setting_item_head">
                        <div>
                            <h3 class="setting_item_title">2. Hiển Thị Sổ Chúc Phúc Điện Tử (Guestbook)</h3>
                            <p class="setting_item_desc">Quyết định xem lời chúc của khách mời có được đăng công khai trên thiệp cưới hay không.</p>
                        </div>
                    </div>
                    <div class="privacy_radio_group">
                        <label class="privacy_radio_label">
                            <input type="radio" name="wishes_visibility" value="public" {{ $settings['wishes_visibility'] === 'public' ? 'checked' : '' }}>
                            <span><strong>Công khai trên thiệp cưới</strong> &mdash; Mọi khách mời có thể đọc lời chúc phúc cùng chia sẻ niềm vui.</span>
                        </label>
                        <label class="privacy_radio_label">
                            <input type="radio" name="wishes_visibility" value="couple_only" {{ $settings['wishes_visibility'] === 'couple_only' ? 'checked' : '' }}>
                            <span><strong>Chế độ riêng tư (Chỉ cô dâu chú rể đọc được)</strong> &mdash; Lời chúc chỉ lưu trong trang quản trị tài khoản.</span>
                        </label>
                    </div>
                </div>

                {{-- C. Kiểm duyệt ảnh khách chụp Photo Drop --}}
                <div class="privacy_setting_item">
                    <div class="setting_item_head">
                        <div>
                            <h3 class="setting_item_title">3. Kiểm Duyệt Album Ảnh Khách Chụp (Live Photo Drop)</h3>
                            <p class="setting_item_desc">Bảo vệ màn hình chiếu đám cưới khỏi các hình ảnh không phù hợp hoặc nhầm lẫn.</p>
                        </div>
                    </div>
                    <div class="privacy_radio_group">
                        <label class="privacy_radio_label">
                            <input type="radio" name="photo_drop_moderation" value="require_approval" {{ $settings['photo_drop_moderation'] === 'require_approval' ? 'checked' : '' }}>
                            <span><strong>Cần cô dâu chú rể duyệt trước (Khuyên dùng)</strong> &mdash; Ảnh chỉ xuất hiện sau khi được xác nhận.</span>
                        </label>
                        <label class="privacy_radio_label">
                            <input type="radio" name="photo_drop_moderation" value="auto_approve" {{ $settings['photo_drop_moderation'] === 'auto_approve' ? 'checked' : '' }}>
                            <span><strong>Tự động hiển thị ngay</strong> &mdash; Ảnh khách tải lên sẽ xuất hiện tức thời trên tường kỷ niệm.</span>
                        </label>
                    </div>
                </div>

                {{-- D. Chính sách lưu trữ & tự động tiêu hủy SĐT khách --}}
                <div class="privacy_setting_item">
                    <div class="setting_item_head">
                        <div>
                            <h3 class="setting_item_title">4. Chính Sách Lưu Trữ &amp; Tiêu Hủy Số Điện Thoại Sau Cưới (Nghị định 13)</h3>
                            <p class="setting_item_desc">Tuân thủ "Quyền được lãng quên" &mdash; Hệ thống tự động ẩn hoặc xóa số điện thoại khách mời sau ngày cưới.</p>
                        </div>
                    </div>
                    <div class="privacy_radio_group">
                        <label class="privacy_radio_label">
                            <input type="radio" name="phone_retention_days" value="90" {{ (int)$settings['phone_retention_days'] === 90 ? 'checked' : '' }}>
                            <span><strong>Sau 90 ngày kể từ ngày cưới (Khuyên dùng tiêu chuẩn)</strong> &mdash; Đủ thời gian gửi lời cảm ơn và lưu kỷ niệm.</span>
                        </label>
                        <label class="privacy_radio_label">
                            <input type="radio" name="phone_retention_days" value="30" {{ (int)$settings['phone_retention_days'] === 30 ? 'checked' : '' }}>
                            <span><strong>Sau 30 ngày kể từ ngày cưới</strong> &mdash; Bảo vệ thông tin liên lạc tối đa.</span>
                        </label>
                        <label class="privacy_radio_label">
                            <input type="radio" name="phone_retention_days" value="0" {{ (int)$settings['phone_retention_days'] === 0 ? 'checked' : '' }}>
                            <span><strong>Lưu trữ vĩnh viễn trong Két Kỷ Niệm (Memory Vault)</strong> &mdash; Không tự động xóa danh bạ.</span>
                        </label>
                    </div>
                </div>

                <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
                    <button type="submit" class="privacy_save_btn">
                        💾 Lưu Cấu Hình Quyền Riêng Tư
                    </button>
                </div>
            </form>
        </div>

        {{-- 3. Compliance Checklist Card --}}
        <div class="mayhen_privacy_checks_card">
            <h2 class="form_card_title" style="margin-bottom: 1rem;">Bảng Kiểm Định Tuân Thủ Bảo Vệ Dữ Liệu Cá Nhân</h2>
            <ul class="compliance_list">
                @foreach($report['checks'] as $chk)
                    <li class="compliance_item {{ $chk['passed'] ? 'compliance_item--pass' : 'compliance_item--warn' }}">
                        <span style="font-size: 1.25rem; line-height: 1;">
                            {{ $chk['passed'] ? '🛡️' : '⚠️' }}
                        </span>
                        <div>
                            <strong>{{ $chk['label'] }}</strong>
                            <div style="font-size: 0.82rem; margin-top: 2px;">{{ $chk['description'] }}</div>
                            @if(!$chk['passed'])
                                <div style="font-size: 0.78rem; font-style: italic; margin-top: 2px; color: #E65100;">
                                    Gợi ý: {{ $chk['recommendation'] }}
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
