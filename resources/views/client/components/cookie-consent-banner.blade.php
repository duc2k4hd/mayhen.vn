@php
    $hasCookieConsent = request()->cookies->has(\App\Http\Controllers\Client\CookieConsentController::COOKIE_NAME);
@endphp
<div id="mayhenCookieBanner"
     class="mayhen_client_cookie_banner {{ $hasCookieConsent ? '' : 'is-visible' }}"
     data-consent-url="{{ route('api.cookie_consent.store') }}"
     role="dialog"
     aria-live="polite"
     aria-label="Thông báo Cookie và Bảo vệ Dữ liệu">
    <div class="mayhen_client_cookie_inner">
        <div class="mayhen_client_cookie_text">
            <strong class="mayhen_client_cookie_title">Thông báo về Cookie &amp; Thu thập Dữ liệu Người dùng</strong>
            <p class="mayhen_client_cookie_desc">
                Mây Hẹn sử dụng Cookie và ghi nhận dữ liệu sử dụng công cụ để vận hành ổn định, nâng cao trải nghiệm và lưu trữ các đường dẫn bạn tạo trong vòng <strong>6 tháng</strong> (sau 6 tháng dữ liệu chi tiết được tự động xóa và chỉ giữ thông tin phân tích). Xem chi tiết tại
                <a href="{{ route('policy.cookies') }}">Chính sách Cookie</a>,
                <a href="{{ route('privacy') }}">Chính sách Bảo mật</a> và
                <a href="{{ route('terms') }}">Điều khoản Dịch vụ</a>.
            </p>
        </div>
        <div class="mayhen_client_cookie_actions">
            <button type="button" class="mayhen_client_cookie_btn mayhen_client_cookie_btn--ghost" data-cookie-action="customize">Tùy chỉnh</button>
            <button type="button" class="mayhen_client_cookie_btn mayhen_client_cookie_btn--outline" data-cookie-action="essential">Chỉ thiết yếu</button>
            <button type="button" class="mayhen_client_cookie_btn mayhen_client_cookie_btn--primary" data-cookie-action="accept-all">Đồng ý tất cả</button>
        </div>
    </div>

    <div id="mayhenCookiePrefsPanel" class="mayhen_client_cookie_prefs" hidden>
        <div class="mayhen_client_cookie_prefs_grid">
            <label class="mayhen_client_cookie_pref_item">
                <input type="checkbox" checked disabled>
                <span><strong>Thiết yếu (Bắt buộc):</strong> Đăng nhập, bảo mật CSRF và lưu lựa chọn này.</span>
            </label>
            <label class="mayhen_client_cookie_pref_item">
                <input type="checkbox" id="mayhenCookieOptAnalytics" checked>
                <span><strong>Phân tích công cụ:</strong> Thống kê hành vi dùng 9 công cụ để tối ưu tốc độ &amp; trải nghiệm.</span>
            </label>
            <label class="mayhen_client_cookie_pref_item">
                <input type="checkbox" id="mayhenCookieOptPersonalization" checked>
                <span><strong>Cá nhân hóa &amp; AI:</strong> Ghi nhớ tùy chọn vùng miền và hỗ trợ AI Concierge chính xác hơn.</span>
            </label>
            <label class="mayhen_client_cookie_pref_item">
                <input type="checkbox" id="mayhenCookieOptMarketing">
                <span><strong>Ưu đãi &amp; Thông báo:</strong> Nhận gợi ý gói cước và chương trình ưu đãi phù hợp.</span>
            </label>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:10px;">
            <button type="button" class="mayhen_client_cookie_btn mayhen_client_cookie_btn--primary" data-cookie-action="save-custom">Lưu Lựa Chọn Của Tôi</button>
        </div>
    </div>
</div>
