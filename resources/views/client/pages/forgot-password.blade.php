<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#faf7f3">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="Quên mật khẩu Mây Hẹn? Khôi phục tài khoản an toàn để tiếp tục chuẩn bị cho ngày chung đôi.">
  <meta name="robots" content="noindex, follow">
  <title>Quên mật khẩu | {{ config('app.name', 'Mây Hẹn') }}</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="{{ asset('assets/client/css/pages/forgot-password.css') }}">
</head>
<body>
<!-- SVG Icons Symbol Definitions -->
<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="position:absolute;width:0;height:0;overflow:hidden">
  <symbol id="mayhen_forgot_password_icon_mail" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m4 7 8 6 8-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_arrow" viewBox="0 0 24 24"><path d="M4 12h15m-6-6 6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_back" viewBox="0 0 24 24"><path d="M20 12H5m6 6-6-6 6-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_help" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M9.5 9a2.6 2.6 0 0 1 5 1.2c0 1.7-2.5 2-2.5 3.8M12 17.2h.01" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_shield" viewBox="0 0 24 24"><path d="m12 2 8 4v6c0 5-3 8.5-8 10-5-1.5-8-5-8-10V6l8-4Z" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="m8.7 12 2.2 2.3 4.7-4.7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_close" viewBox="0 0 24 24"><path d="M5 5 19 19M19 5 5 19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M19.1 4.9l-1.4 1.4M6.3 17.7l-1.4 1.4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_sliders" viewBox="0 0 24 24"><path d="M5 4v16m7-16v16m7-16v16M2 9h6M9 15h6m1-7h6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_check" viewBox="0 0 24 24"><path d="m5 12.5 4.6 4.5L19 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3.5 2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_lock" viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="11" rx="2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M8 10V7a4 4 0 1 1 8 0v3m-4 4v3" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_chevron" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_copy" viewBox="0 0 24 24"><rect x="8" y="8" width="12" height="12" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2" fill="none" stroke="currentColor" stroke-width="1.6"/></symbol>
</svg>

<main class="mayhen_forgot_password_page" id="mayhen_forgot_password_page" data-theme="light" data-motion="on">
  <div class="mayhen_forgot_password_orbit mayhen_forgot_password_orbit_one"></div>
  <div class="mayhen_forgot_password_orbit mayhen_forgot_password_orbit_two"></div>
  <section class="mayhen_forgot_password_shell" aria-label="Khôi phục mật khẩu tài khoản Mây Hẹn">
    <aside class="mayhen_forgot_password_hero" aria-label="Lời nhắn từ Mây Hẹn">
      <div class="mayhen_forgot_password_hero_top">
        <a class="mayhen_forgot_password_brand" href="{{ route('home') }}" aria-label="{{ config('app.name', 'Mây Hẹn') }} - Trang chủ">
          <span class="mayhen_forgot_password_brand_mark">
            <svg viewBox="0 0 48 48" fill="none" aria-hidden="true"><path d="M24 40C10 30 6 23 6 16a9 9 0 0 1 17.5-3 9 9 0 0 1 17.5 3c0 7-4 14-17 24Z" stroke="currentColor" stroke-width="1.45"/><path d="M24 33V15m0 8-7-5m7 9 7-7" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/><path d="M13 13c5-6 17-7 23 2" stroke="currentColor" stroke-width=".8" stroke-dasharray="2 3"/></svg>
          </span>
          <span><span class="mayhen_forgot_password_brand_name">{{ config('app.name', 'Mây Hẹn') }}</span><span class="mayhen_forgot_password_brand_caption">GIỮ TRỌN KHOẢNH KHẮC</span></span>
        </a>
        <span class="mayhen_forgot_password_hero_stamp">EST. 2026 · LOVE & CARE</span>
      </div>
      <div class="mayhen_forgot_password_hero_mid">
        <span class="mayhen_forgot_password_eyebrow">Một lời nhắn dịu dàng</span>
        <h2 class="mayhen_forgot_password_hero_title">Chuyện chúng mình<br><em>vẫn ở đây...</em></h2>
        <p class="mayhen_forgot_password_hero_description">Có những điều đáng nhớ luôn được nâng niu. Mật khẩu có thể quên, nhưng hành trình yêu thương thì không.</p>
        
        <!-- Artwork SVG thuần không dùng ảnh bên ngoài -->
        <svg class="mayhen_forgot_password_art" viewBox="0 0 410 274" role="img" aria-label="Phong bì thư cưới màu kem có dấu sáp trái tim cùng hoa lá trang trí">
          <defs>
            <linearGradient id="mayhen_forgot_password_art_gradient" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#fffefb"/><stop offset="1" stop-color="#efdfd3"/></linearGradient>
            <linearGradient id="mayhen_forgot_password_wax" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#a36a6e"/><stop offset="1" stop-color="#71454a"/></linearGradient>
            <linearGradient id="mayhen_forgot_password_leaf" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#a3a58a"/><stop offset="1" stop-color="#7f9378"/></linearGradient>
          </defs>
          <ellipse cx="210" cy="249" rx="141" ry="12" fill="#805052" opacity=".08"/>
          <g fill="none" stroke="#a89b82" stroke-width="1.25" stroke-linecap="round">
            <path d="M67 224C25 180 43 110 95 72m-28 152c-2-48 18-97 57-125M346 222c41-55 32-120-5-166m5 166c8-46-4-101-27-120"/>
            <path d="M75 187c-22-4-35-19-37-34m50 6c-16-23-18-42-13-57m27 34c-4-22 3-38 14-47m216 97c24-10 36-30 36-49m-51 12c16-20 19-42 16-58m-29 26c1-20-4-35-15-45"/>
          </g>
          <g fill="url(#mayhen_forgot_password_leaf)" opacity=".67"><path d="M39 151c18 3 24 16 24 25-17-5-24-13-24-25Z"/><path d="M75 101c17 9 20 23 19 30-15-6-21-16-19-30Z"/><path d="M114 89c-4 16-13 22-23 24 3-15 12-22 23-24Z"/><path d="M367 137c-18 8-26 22-25 33 20-6 26-19 25-33Z"/><path d="M336 91c-17 7-22 19-22 29 16-7 23-17 22-29Z"/><path d="M290 72c6 14 17 22 27 23-5-15-14-23-27-23Z"/><path d="M52 195c19 2 24 10 31 22-18-2-26-11-31-22Z"/></g>
          <g fill="#e5ac9f" opacity=".86"><path d="M61 124c4-12 9-10 13-6 10-4 13 2 8 10 4 9-3 12-10 8-8 4-12-2-8-9Z"/><path d="M334 79c2-11 8-13 13-7 10-3 14 3 9 11 4 8-3 12-10 8-7 3-12-3-8-10Z"/><path d="M365 189c-4-11-10-9-13-5-10-3-13 4-8 11-3 9 5 12 11 7 8 3 12-4 9-10Z"/></g>
          <rect x="97" y="47" width="217" height="148" rx="6" fill="#e5cfc1" transform="rotate(-5 205 121)"/>
          <rect x="105" y="42" width="213" height="148" rx="6" fill="#fffdfa" stroke="#d5b8a3" stroke-width="1.4" transform="rotate(-5 205 121)"/>
          <g transform="rotate(-5 205 121)"><path d="M120 61h180v111H120Z" fill="none" stroke="#cdb69d" stroke-width=".85" stroke-dasharray="1.7 3"/><text x="210" y="100" text-anchor="middle" fill="#a58477" font-family="Georgia,serif" font-style="italic" font-size="13">{{ config('app.name', 'Mây Hẹn') }}</text><path d="M176 113h68" stroke="#c9ae91" stroke-width=".8"/><text x="210" y="132" text-anchor="middle" fill="#805052" font-family="Georgia,serif" font-size="17">Một lời nhắc yêu thương</text><text x="210" y="153" text-anchor="middle" fill="#a99483" font-family="Georgia,serif" font-size="10">With love, always</text></g>
          <g><rect x="73" y="120" width="270" height="121" rx="8" fill="url(#mayhen_forgot_password_art_gradient)" stroke="#d2b8a5" stroke-width="1.3"/><path d="m76 123 129 84 135-84" fill="#f6ede4" stroke="#d1b5a3" stroke-width="1.1"/><path d="m75 238 103-81m165 81-111-80" fill="none" stroke="#d0b6a3" stroke-width="1.1"/><path d="m76 239 117-78q12-8 24 0l124 78Z" fill="#fffaf4" stroke="#d3b8a5" stroke-width="1.2"/></g>
          <g><circle cx="207" cy="201" r="23" fill="url(#mayhen_forgot_password_wax)"/><circle cx="207" cy="201" r="18" fill="none" stroke="#e8cbb3" stroke-width=".8"/><path d="M207 212c-15-11-15-19-9-22 4-3 8 0 9 3 2-4 7-6 11-2 6 6 0 14-11 21Z" fill="none" stroke="#f5e0cf" stroke-width="1.6"/></g>
          <g fill="#d4b68e"><circle cx="47" cy="91" r="1.8"/><circle cx="358" cy="52" r="2"/><circle cx="377" cy="222" r="1.8"/><path d="m40 105 1 4 4 1-4 1-1 4-1-4-4-1 4-1Z"/><path d="m375 90 1 4 4 1-4 1-1 4-1-4-4-1 4-1Z"/></g>
        </svg>
      </div>
      <div class="mayhen_forgot_password_hero_bottom"><span class="mayhen_forgot_password_hero_bottom_line"></span><span class="mayhen_forgot_password_hero_bottom_note">Love is in the details ♡</span><span class="mayhen_forgot_password_hero_bottom_line"></span></div>
    </aside>

    <div class="mayhen_forgot_password_panel">
      <header class="mayhen_forgot_password_panel_top">
        <nav class="mayhen_forgot_password_breadcrumb" aria-label="Đường dẫn">
          <a href="{{ route('login') }}">Tài khoản</a> <span aria-hidden="true">/</span> <strong>Khôi phục mật khẩu</strong>
        </nav>
        <div class="mayhen_forgot_password_panel_top_actions">
          <button type="button" class="mayhen_forgot_password_icon_button" data-modal="settings" aria-label="Tuỳ chỉnh giao diện" title="Tùy chỉnh giao diện"><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_sliders"/></svg></button>
          <button type="button" class="mayhen_forgot_password_icon_button" id="mayhen_forgot_password_theme_toggle" aria-label="Đổi giao diện sáng/tối" title="Đổi giao diện sáng/tối"><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_sun"/></svg></button>
        </div>
      </header>

      <div class="mayhen_forgot_password_panel_body">
        <section id="mayhen_forgot_password_request_stage" aria-labelledby="mayhen_forgot_password_request_heading">
          <span class="mayhen_forgot_password_decoration"><svg viewBox="0 0 70 42" fill="none" aria-hidden="true"><path d="M7 32c14-16 23-14 29-2 7-13 18-15 28-2M35 29c-1-8-5-14-12-18m12 18c0-10 6-18 15-20" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/><path d="M15 18c8 1 9 6 11 11-7-1-10-5-11-11Zm8-15c7 1 8 6 8 11-6-2-8-5-8-11Zm30-1c-8 2-10 7-10 12 7-2 10-6 10-12Zm5 15c-7 1-10 5-11 11 7-1 10-5 11-11Z" fill="currentColor" opacity=".6"/></svg></span>
          <p class="mayhen_forgot_password_panel_kicker">Điều nhỏ xíu có thể quên...</p>
          <h1 class="mayhen_forgot_password_heading" id="mayhen_forgot_password_request_heading">Quên mật khẩu?</h1>
          <p class="mayhen_forgot_password_intro">Không sao cả! Hãy nhập địa chỉ email bạn dùng tại Mây Hẹn. Chúng mình sẽ hướng dẫn bạn đặt lại mật khẩu an toàn.</p>

          <form id="mayhen_forgot_password_form" action="{{ route('password.email') }}" method="post" novalidate>
            @csrf
            <div class="mayhen_forgot_password_field">
              <label class="mayhen_forgot_password_label" for="mayhen_forgot_password_email">Địa chỉ email của bạn</label>
              <div class="mayhen_forgot_password_input_wrap">
                <svg class="mayhen_forgot_password_input_icon" aria-hidden="true"><use href="#mayhen_forgot_password_icon_mail"/></svg>
                <input class="mayhen_forgot_password_input" 
                       id="mayhen_forgot_password_email" 
                       name="email" 
                       type="email" 
                       value="{{ old('email') }}"
                       autocomplete="email" 
                       inputmode="email" 
                       autocapitalize="none" 
                       spellcheck="false" 
                       maxlength="254" 
                       placeholder="tenban@example.com" 
                       required 
                       aria-describedby="mayhen_forgot_password_error mayhen_forgot_password_email_hint">
                <button class="mayhen_forgot_password_input_clear" type="button" id="mayhen_forgot_password_clear" aria-label="Xóa địa chỉ email" hidden><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_close"/></svg></button>
              </div>
              <span class="mayhen_forgot_password_field_error" id="mayhen_forgot_password_error" aria-live="polite">
                @error('email') {{ $message }} @enderror
              </span>
            </div>
            <p class="mayhen_forgot_password_hint" id="mayhen_forgot_password_email_hint">Liên kết đặt lại mật khẩu có hạn dùng trong 48 tiếng. Đừng chia sẻ liên kết với người khác.</p>
            <button class="mayhen_forgot_password_primary" type="submit" id="mayhen_forgot_password_submit">
              <span class="mayhen_forgot_password_primary_spinner" aria-hidden="true"></span>
              <span id="mayhen_forgot_password_submit_text">Gửi liên kết khôi phục</span>
              <svg class="mayhen_forgot_password_primary_arrow" aria-hidden="true"><use href="#mayhen_forgot_password_icon_arrow"/></svg>
            </button>
          </form>

          <a class="mayhen_forgot_password_secondary_link" href="{{ route('login') }}"><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_back"/></svg>Quay lại đăng nhập</a>
          
          <div class="mayhen_forgot_password_separator">Bạn cần trợ giúp?</div>
          <div class="mayhen_forgot_password_help_buttons">
            <button type="button" class="mayhen_forgot_password_help_button" data-modal="noemail"><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_mail"/></svg>Không nhận được email?</button>
            <button type="button" class="mayhen_forgot_password_help_button" data-modal="guide"><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_help"/></svg>Xem hướng dẫn</button>
            <button type="button" class="mayhen_forgot_password_help_button" data-modal="security"><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_shield"/></svg>Mẹo bảo mật</button>
            <button type="button" class="mayhen_forgot_password_help_button" data-modal="support"><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_help"/></svg>Liên hệ hỗ trợ</button>
          </div>
          <button type="button" class="mayhen_forgot_password_preview" id="mayhen_forgot_password_preview">Xem thử giao diện khi gửi thành công (demo)</button>
        </section>

        <section class="mayhen_forgot_password_success" id="mayhen_forgot_password_success_stage" aria-live="polite" hidden>
          <span class="mayhen_forgot_password_success_emblem"><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_check"/></svg></span>
          <span class="mayhen_forgot_password_demo_badge" id="mayhen_forgot_password_demo_badge" hidden>BẢN XEM TRƯỚC — CHƯA GỬI EMAIL</span>
          <p class="mayhen_forgot_password_panel_kicker">Một bước nữa thôi...</p>
          <h2 class="mayhen_forgot_password_heading">Kiểm tra hộp thư nhé!</h2>
          <p class="mayhen_forgot_password_intro" id="mayhen_forgot_password_success_description">Nếu địa chỉ email này có tài khoản, bạn sẽ nhận được hướng dẫn đặt lại mật khẩu từ no-reply@mayhen.vn.</p>
          <div class="mayhen_forgot_password_success_email" id="mayhen_forgot_password_success_email"></div>
          <div class="mayhen_forgot_password_success_tips">
            <div class="mayhen_forgot_password_success_tip"><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_check"/></svg>Kiểm tra cả thư mục Spam, Quảng cáo hoặc Thư rác.</div>
            <div class="mayhen_forgot_password_success_tip"><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_clock"/></svg>Liên kết có hiệu lực trong 48 tiếng và dùng 1 lần duy nhất.</div>
            <div class="mayhen_forgot_password_success_tip"><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_lock"/></svg>Không cung cấp mã hoặc liên kết khôi phục cho người khác.</div>
          </div>
          <button type="button" class="mayhen_forgot_password_primary" id="mayhen_forgot_password_edit_email">Dùng địa chỉ email khác</button>
          <button type="button" class="mayhen_forgot_password_resend" id="mayhen_forgot_password_resend" hidden>Gửi lại email <span id="mayhen_forgot_password_resend_timer"></span></button>
          <a class="mayhen_forgot_password_secondary_link" href="{{ route('login') }}"><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_back"/></svg>Về trang đăng nhập</a>
          <button class="mayhen_forgot_password_preview" type="button" data-modal="noemail">Vẫn chưa thấy email? Xem cách xử lý</button>
        </section>
      </div>

      <footer class="mayhen_forgot_password_panel_bottom">
        <div class="mayhen_forgot_password_alert"><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_shield"/></svg><span><strong>An tâm cùng Mây Hẹn.</strong> Để bảo vệ quyền riêng tư, hệ thống không công khai email nào đã đăng ký tài khoản.</span></div>
        <div class="mayhen_forgot_password_legal">
          <span>© 2026 {{ config('app.name', 'Mây Hẹn') }}. Crafted with ♡</span>
          <span class="mayhen_forgot_password_legal_links">
            <a href="{{ route('register') }}">Đăng ký</a>
            <button class="mayhen_forgot_password_text_button" type="button" data-modal="privacy">Quyền riêng tư</button>
          </span>
        </div>
      </footer>
    </div>
  </section>
  <div role="status" aria-live="polite" class="mayhen_forgot_password_toast" id="mayhen_forgot_password_toast" data-open="false"></div>
</main>

<!-- Modal backdrop & Dialog -->
<div class="mayhen_forgot_password_modal_backdrop" id="mayhen_forgot_password_modal_backdrop" hidden>
  <section class="mayhen_forgot_password_modal" id="mayhen_forgot_password_modal" role="dialog" aria-modal="true" aria-labelledby="mayhen_forgot_password_modal_title" tabindex="-1">
    <button class="mayhen_forgot_password_icon_button mayhen_forgot_password_modal_close" id="mayhen_forgot_password_modal_close" type="button" aria-label="Đóng cửa sổ"><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_close"/></svg></button>
    <div class="mayhen_forgot_password_modal_ornament" id="mayhen_forgot_password_modal_ornament"><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_help"/></svg></div>
    <p class="mayhen_forgot_password_modal_eyebrow" id="mayhen_forgot_password_modal_eyebrow"></p>
    <h2 class="mayhen_forgot_password_modal_title" id="mayhen_forgot_password_modal_title"></h2>
    <div id="mayhen_forgot_password_modal_content"></div>
  </section>
</div>

<!-- Modal Templates -->
<template id="mayhen_forgot_password_template_guide">
  <p class="mayhen_forgot_password_modal_lead">Chỉ vài thao tác là bạn có thể quay lại với những kế hoạch ngọt ngào của mình.</p>
  <ol class="mayhen_forgot_password_modal_list">
    <li class="mayhen_forgot_password_modal_item"><span class="mayhen_forgot_password_modal_number">01</span><span>Nhập email đã sử dụng để tạo tài khoản Mây Hẹn.</span></li>
    <li class="mayhen_forgot_password_modal_item"><span class="mayhen_forgot_password_modal_number">02</span><span>Mở email khôi phục được gửi từ <strong>no-reply@mayhen.vn</strong> và kiểm tra liên kết an toàn.</span></li>
    <li class="mayhen_forgot_password_modal_item"><span class="mayhen_forgot_password_modal_number">03</span><span>Đặt một mật khẩu mới đủ mạnh (tối thiểu 8 ký tự cả chữ và số); sau đó đăng nhập lại.</span></li>
  </ol>
  <p class="mayhen_forgot_password_modal_callout">Liên kết khôi phục có hiệu lực trong 48 tiếng và tự động vô hiệu hóa ngay sau khi đổi mật khẩu thành công.</p>
  <button type="button" class="mayhen_forgot_password_primary mayhen_forgot_password_modal_action" data-close-modal="true">Mình đã hiểu</button>
</template>

<template id="mayhen_forgot_password_template_noemail">
  <p class="mayhen_forgot_password_modal_lead">Nếu chưa nhận được thư, bạn có thể kiểm tra theo những cách dưới đây.</p>
  <div class="mayhen_forgot_password_accordion">
    <details class="mayhen_forgot_password_accordion_item" open><summary class="mayhen_forgot_password_accordion_summary">01. Kiểm tra Spam hoặc Quảng cáo <svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_chevron"/></svg></summary><div class="mayhen_forgot_password_accordion_answer">Thư đôi khi được lọc vào Spam/Junk, Promotions hoặc Other. Hãy tìm từ khóa “Mây Hẹn” hoặc “no-reply@mayhen.vn” trong hộp thư.</div></details>
    <details class="mayhen_forgot_password_accordion_item"><summary class="mayhen_forgot_password_accordion_summary">02. Kiểm tra địa chỉ email <svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_chevron"/></svg></summary><div class="mayhen_forgot_password_accordion_answer">Xem lại lỗi gõ, dấu chấm, tên miền như gmail.com và địa chỉ email đã dùng lúc đăng ký. Bạn có thể quay về form để nhập lại.</div></details>
    <details class="mayhen_forgot_password_accordion_item"><summary class="mayhen_forgot_password_accordion_summary">03. Liên kết hết hạn? <svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_chevron"/></svg></summary><div class="mayhen_forgot_password_accordion_answer">Liên kết có hạn dùng trong 48 tiếng. Nếu đã quá hạn, bạn hãy tạo yêu cầu mới. Không dùng lại liên kết cũ đã đổi thành công.</div></details>
    <details class="mayhen_forgot_password_accordion_item"><summary class="mayhen_forgot_password_accordion_summary">04. Vẫn không khôi phục được? <svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_chevron"/></svg></summary><div class="mayhen_forgot_password_accordion_answer">Mở mục “Liên hệ hỗ trợ” để gửi thông tin cho đội ngũ Mây Hẹn. Tuyệt đối không gửi mật khẩu cho bất kỳ ai.</div></details>
  </div>
  <button type="button" class="mayhen_forgot_password_primary mayhen_forgot_password_modal_action" data-close-modal="true">Đã hiểu, quay lại</button>
</template>

<template id="mayhen_forgot_password_template_security">
  <p class="mayhen_forgot_password_modal_lead">Bảo vệ tài khoản cũng là bảo vệ những thông tin riêng tư trong hành trình chuẩn bị ngày cưới.</p>
  <ul class="mayhen_forgot_password_modal_list">
    <li class="mayhen_forgot_password_modal_item"><span class="mayhen_forgot_password_modal_number">✓</span><span>Dùng mật khẩu dài tối thiểu 8 ký tự, kết hợp cả chữ hoa, chữ thường và chữ số.</span></li>
    <li class="mayhen_forgot_password_modal_item"><span class="mayhen_forgot_password_modal_number">✓</span><span>Chỉ nhập mật khẩu trên website chính thức của Mây Hẹn (kết nối bảo mật HTTPS).</span></li>
    <li class="mayhen_forgot_password_modal_item"><span class="mayhen_forgot_password_modal_number">✓</span><span>Không gửi mã hoặc liên kết khôi phục cho người lạ qua mạng xã hội.</span></li>
    <li class="mayhen_forgot_password_modal_item"><span class="mayhen_forgot_password_modal_number">✓</span><span>Đổi mật khẩu định kỳ nếu dùng chung máy tính công cộng.</span></li>
  </ul>
  <p class="mayhen_forgot_password_modal_callout">Đội ngũ Mây Hẹn không bao giờ chủ động gọi điện hoặc nhắn tin hỏi mật khẩu của bạn.</p>
  <button type="button" class="mayhen_forgot_password_primary mayhen_forgot_password_modal_action" data-close-modal="true">Mình sẽ ghi nhớ</button>
</template>

<template id="mayhen_forgot_password_template_support">
  <p class="mayhen_forgot_password_modal_lead">Bạn có thể chuẩn bị mô tả ngắn gọn để đội ngũ hỗ trợ hiểu tình huống. Đừng cung cấp mật khẩu hoặc liên kết khôi phục.</p>
  <label class="mayhen_forgot_password_modal_field" for="mayhen_forgot_password_support_message">Mô tả vấn đề</label>
  <textarea class="mayhen_forgot_password_modal_textarea" id="mayhen_forgot_password_support_message" maxlength="1000" placeholder="Ví dụ: Tôi không nhận được email khôi phục dù đã kiểm tra hộp thư Spam..."></textarea>
  <p class="mayhen_forgot_password_modal_small">Nội dung có thể sao chép để dán vào kênh hỗ trợ chính thức của Mây Hẹn.</p>
  <button class="mayhen_forgot_password_primary mayhen_forgot_password_modal_action" type="button" id="mayhen_forgot_password_support_copy"><svg aria-hidden="true" width="17" height="17"><use href="#mayhen_forgot_password_icon_copy"/></svg>Sao chép nội dung hỗ trợ</button>
  <div class="mayhen_forgot_password_modal_footer"><a class="mayhen_forgot_password_modal_link" id="mayhen_forgot_password_support_link" href="{{ route('contact') }}">Đến trang liên hệ →</a></div>
</template>

<template id="mayhen_forgot_password_template_privacy">
  <p class="mayhen_forgot_password_modal_lead">Trang khôi phục ưu tiên riêng tư: không cho biết công khai một email đã tồn tại trong hệ thống hay chưa.</p>
  <ul class="mayhen_forgot_password_modal_list">
    <li class="mayhen_forgot_password_modal_item"><span class="mayhen_forgot_password_modal_number">01</span><span>Địa chỉ email chỉ được dùng cho mục đích xác thực và gửi liên kết đổi mật khẩu.</span></li>
    <li class="mayhen_forgot_password_modal_item"><span class="mayhen_forgot_password_modal_number">02</span><span>Hệ thống áp dụng giới hạn tần suất gửi để ngăn chặn tấn công tự động hoặc quấy rối.</span></li>
    <li class="mayhen_forgot_password_modal_item"><span class="mayhen_forgot_password_modal_number">03</span><span>Mã bảo mật được băm mật mã học (cryptographic hash) an toàn trong cơ sở dữ liệu.</span></li>
  </ul>
  <p class="mayhen_forgot_password_modal_callout">Mọi dữ liệu cá nhân của cô dâu chú rể tại Mây Hẹn được bảo mật tuân thủ Luật Bảo vệ dữ liệu cá nhân.</p>
  <button type="button" class="mayhen_forgot_password_primary mayhen_forgot_password_modal_action" data-close-modal="true">Đóng</button>
</template>

<template id="mayhen_forgot_password_template_settings">
  <p class="mayhen_forgot_password_modal_lead">Tùy chỉnh hiển thị để đọc thoải mái hơn. Những thay đổi chỉ áp dụng cho trang này.</p>
  <div class="mayhen_forgot_password_setting">
    <div><span class="mayhen_forgot_password_setting_label">Giao diện</span><span class="mayhen_forgot_password_setting_caption">Sáng hoặc tối tùy theo mắt bạn</span></div>
    <div class="mayhen_forgot_password_segment" role="group" aria-label="Chọn giao diện">
      <button class="mayhen_forgot_password_segment_button" type="button" data-set-theme="light" aria-pressed="true">Sáng</button>
      <button class="mayhen_forgot_password_segment_button" type="button" data-set-theme="dark" aria-pressed="false">Tối</button>
    </div>
  </div>
  <div class="mayhen_forgot_password_setting">
    <div><span class="mayhen_forgot_password_setting_label">Cỡ chữ</span><span class="mayhen_forgot_password_setting_caption">Tăng giảm theo nhu cầu</span></div>
    <div class="mayhen_forgot_password_segment" role="group" aria-label="Chọn cỡ chữ">
      <button class="mayhen_forgot_password_segment_button" type="button" data-set-scale="0.94" aria-pressed="false">A−</button>
      <button class="mayhen_forgot_password_segment_button" type="button" data-set-scale="1" aria-pressed="true">A</button>
      <button class="mayhen_forgot_password_segment_button" type="button" data-set-scale="1.1" aria-pressed="false">A+</button>
    </div>
  </div>
  <div class="mayhen_forgot_password_setting">
    <div><span class="mayhen_forgot_password_setting_label">Hiệu ứng chuyển động</span><span class="mayhen_forgot_password_setting_caption">Có thể tắt hoàn toàn nếu cần</span></div>
    <button type="button" role="switch" aria-checked="true" class="mayhen_forgot_password_switch" id="mayhen_forgot_password_motion_switch" aria-label="Bật hoặc tắt chuyển động"></button>
  </div>
  <button type="button" class="mayhen_forgot_password_primary mayhen_forgot_password_modal_action" data-close-modal="true" style="margin-top:20px">Hoàn tất</button>
</template>

<script>
  window.MAYHEN_FORGOT_PASSWORD_CONFIG = {
    resetEndpoint: "{{ route('password.email') }}",
    homeUrl: "{{ route('home') }}",
    loginUrl: "{{ route('login') }}",
    registerUrl: "{{ route('register') }}",
    supportUrl: "{{ route('contact') }}",
    resendCooldownSeconds: 60,
    requestTimeoutMs: 12000
  };
  @if(session('sent_email'))
    window.MAYHEN_SENT_EMAIL = @json(session('sent_email'));
  @endif
</script>
<script src="{{ asset('assets/client/js/pages/forgot-password.js') }}"></script>
</body>
</html>
