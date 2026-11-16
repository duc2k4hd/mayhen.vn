<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#faf7f3">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="Thiết lập mật khẩu mới cho tài khoản Mây Hẹn để tiếp tục hành trình chuẩn bị ngày chung đôi.">
  <meta name="robots" content="noindex, nofollow">
  <title>Đặt lại mật khẩu | {{ config('app.name', 'Mây Hẹn') }}</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="{{ asset('assets/client/css/pages/forgot-password.css') }}">
  <style>
    /* Styling bổ trợ cho màn hình đặt lại mật khẩu */
    .mayhen_reset_input_toggle {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: transparent;
      border: none;
      color: #ad8f82;
      padding: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }
    .mayhen_reset_input_toggle:hover { color: var(--mayhen-forgot-password-rose); }
    .mayhen_reset_strength_bar {
      height: 4px;
      background: #E8DCD6;
      border-radius: 2px;
      margin-top: 8px;
      overflow: hidden;
      display: flex;
      gap: 3px;
    }
    .mayhen_reset_strength_step {
      flex: 1;
      height: 100%;
      background: transparent;
      border-radius: 2px;
      transition: background-color 0.25s;
    }
    .mayhen_reset_requirements {
      margin: 10px 0 20px;
      padding: 10px 14px;
      background: #FDF9F6;
      border: 1px solid #EBDED6;
      border-radius: 9px;
      font-size: 11.5px;
      color: #7D6B66;
      line-height: 1.6;
    }
    .mayhen_reset_req_item {
      display: flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 4px;
    }
    .mayhen_reset_req_item:last-child { margin-bottom: 0; }
    .mayhen_reset_req_icon { font-size: 11px; }
  </style>
</head>
<body>
<!-- SVG Icons Symbol Definitions -->
<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="position:absolute;width:0;height:0;overflow:hidden">
  <symbol id="mayhen_forgot_password_icon_lock" viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="11" rx="2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M8 10V7a4 4 0 1 1 8 0v3m-4 4v3" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_mail" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m4 7 8 6 8-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_arrow" viewBox="0 0 24 24"><path d="M4 12h15m-6-6 6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_back" viewBox="0 0 24 24"><path d="M20 12H5m6 6-6-6 6-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_shield" viewBox="0 0 24 24"><path d="m12 2 8 4v6c0 5-3 8.5-8 10-5-1.5-8-5-8-10V6l8-4Z" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="m8.7 12 2.2 2.3 4.7-4.7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_check" viewBox="0 0 24 24"><path d="m5 12.5 4.6 4.5L19 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="mayhen_forgot_password_icon_eye" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" fill="none" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.7"/></symbol>
</svg>

<main class="mayhen_forgot_password_page" id="mayhen_forgot_password_page" data-theme="light">
  <div class="mayhen_forgot_password_orbit mayhen_forgot_password_orbit_one"></div>
  <div class="mayhen_forgot_password_orbit mayhen_forgot_password_orbit_two"></div>
  <section class="mayhen_forgot_password_shell" aria-label="Thiết lập mật khẩu mới">
    <aside class="mayhen_forgot_password_hero" aria-label="Lời nhắn từ Mây Hẹn">
      <div class="mayhen_forgot_password_hero_top">
        <a class="mayhen_forgot_password_brand" href="{{ route('home') }}" aria-label="{{ config('app.name', 'Mây Hẹn') }} - Trang chủ">
          <span class="mayhen_forgot_password_brand_mark">
            <svg viewBox="0 0 48 48" fill="none" aria-hidden="true"><path d="M24 40C10 30 6 23 6 16a9 9 0 0 1 17.5-3 9 9 0 0 1 17.5 3c0 7-4 14-17 24Z" stroke="currentColor" stroke-width="1.45"/><path d="M24 33V15m0 8-7-5m7 9 7-7" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/><path d="M13 13c5-6 17-7 23 2" stroke="currentColor" stroke-width=".8" stroke-dasharray="2 3"/></svg>
          </span>
          <span><span class="mayhen_forgot_password_brand_name">{{ config('app.name', 'Mây Hẹn') }}</span><span class="mayhen_forgot_password_brand_caption">GIỮ TRỌN KHOẢNH KHẮC</span></span>
        </a>
        <span class="mayhen_forgot_password_hero_stamp">SECURITY · VERIFIED</span>
      </div>
      <div class="mayhen_forgot_password_hero_mid">
        <span class="mayhen_forgot_password_eyebrow">Bước cuối cùng</span>
        <h2 class="mayhen_forgot_password_hero_title">Thiết lập mật khẩu<br><em>mới an toàn</em></h2>
        <p class="mayhen_forgot_password_hero_description">Mật khẩu mới giúp bảo vệ trọn vẹn thông tin ngày cưới, danh sách khách mời và những khoảnh khắc quý giá của hai bạn.</p>
        
        <!-- Artwork SVG Quiet Luxury -->
        <svg class="mayhen_forgot_password_art" viewBox="0 0 410 274" role="img" aria-label="Phong bì thư cưới có dấu sáp bảo mật">
          <defs>
            <linearGradient id="mayhen_reset_art_gradient" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#fffefb"/><stop offset="1" stop-color="#efdfd3"/></linearGradient>
            <linearGradient id="mayhen_reset_wax" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#a36a6e"/><stop offset="1" stop-color="#71454a"/></linearGradient>
            <linearGradient id="mayhen_reset_leaf" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#a3a58a"/><stop offset="1" stop-color="#7f9378"/></linearGradient>
          </defs>
          <ellipse cx="210" cy="249" rx="141" ry="12" fill="#805052" opacity=".08"/>
          <rect x="97" y="47" width="217" height="148" rx="6" fill="#e5cfc1" transform="rotate(-5 205 121)"/>
          <rect x="105" y="42" width="213" height="148" rx="6" fill="#fffdfa" stroke="#d5b8a3" stroke-width="1.4" transform="rotate(-5 205 121)"/>
          <g transform="rotate(-5 205 121)"><text x="210" y="105" text-anchor="middle" fill="#805052" font-family="Georgia,serif" font-size="16">Khởi đầu ngọt ngào</text><text x="210" y="132" text-anchor="middle" fill="#a99483" font-family="Georgia,serif" font-size="11">Bảo mật &amp; An tâm</text></g>
          <g><rect x="73" y="120" width="270" height="121" rx="8" fill="url(#mayhen_reset_art_gradient)" stroke="#d2b8a5" stroke-width="1.3"/><path d="m76 123 129 84 135-84" fill="#f6ede4" stroke="#d1b5a3" stroke-width="1.1"/><path d="m75 238 103-81m165 81-111-80" fill="none" stroke="#d0b6a3" stroke-width="1.1"/><path d="m76 239 117-78q12-8 24 0l124 78Z" fill="#fffaf4" stroke="#d3b8a5" stroke-width="1.2"/></g>
          <g><circle cx="207" cy="201" r="23" fill="url(#mayhen_reset_wax)"/><circle cx="207" cy="201" r="18" fill="none" stroke="#e8cbb3" stroke-width=".8"/><path d="M207 212c-15-11-15-19-9-22 4-3 8 0 9 3 2-4 7-6 11-2 6 6 0 14-11 21Z" fill="none" stroke="#f5e0cf" stroke-width="1.6"/></g>
        </svg>
      </div>
      <div class="mayhen_forgot_password_hero_bottom"><span class="mayhen_forgot_password_hero_bottom_line"></span><span class="mayhen_forgot_password_hero_bottom_note">Bảo mật trọn vẹn ♡</span><span class="mayhen_forgot_password_hero_bottom_line"></span></div>
    </aside>

    <div class="mayhen_forgot_password_panel">
      <header class="mayhen_forgot_password_panel_top">
        <nav class="mayhen_forgot_password_breadcrumb" aria-label="Đường dẫn">
          <a href="{{ route('login') }}">Tài khoản</a> <span aria-hidden="true">/</span> <strong>Tạo mật khẩu mới</strong>
        </nav>
      </header>

      <div class="mayhen_forgot_password_panel_body">
        <section id="mayhen_reset_password_stage">
          <p class="mayhen_forgot_password_panel_kicker">Gần xong rồi...</p>
          <h1 class="mayhen_forgot_password_heading">Mật khẩu mới</h1>
          <p class="mayhen_forgot_password_intro">Vui lòng nhập mật khẩu mới đủ mạnh để bảo vệ tài khoản của bạn.</p>

          <form action="{{ route('password.update') }}" method="post" novalidate id="mayhen_reset_form">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mayhen_forgot_password_field" style="margin-bottom: 14px;">
              <label class="mayhen_forgot_password_label" for="mayhen_reset_email">Địa chỉ email</label>
              <div class="mayhen_forgot_password_input_wrap">
                <svg class="mayhen_forgot_password_input_icon" aria-hidden="true"><use href="#mayhen_forgot_password_icon_mail"/></svg>
                <input class="mayhen_forgot_password_input" 
                       id="mayhen_reset_email" 
                       name="email" 
                       type="email" 
                       value="{{ old('email', $email) }}" 
                       readonly 
                       style="background: #FAF7F3; color: #76615B; cursor: not-allowed;">
              </div>
              @error('email')
                <span class="mayhen_forgot_password_field_error">{{ $message }}</span>
              @enderror
            </div>

            <div class="mayhen_forgot_password_field" style="margin-bottom: 14px;">
              <label class="mayhen_forgot_password_label" for="mayhen_new_password">Mật khẩu mới</label>
              <div class="mayhen_forgot_password_input_wrap">
                <svg class="mayhen_forgot_password_input_icon" aria-hidden="true"><use href="#mayhen_forgot_password_icon_lock"/></svg>
                <input class="mayhen_forgot_password_input" 
                       id="mayhen_new_password" 
                       name="password" 
                       type="password" 
                       required 
                       placeholder="Nhập ít nhất 8 ký tự..." 
                       autocomplete="new-password">
                <button type="button" class="mayhen_reset_input_toggle" id="mayhen_toggle_pwd" aria-label="Ẩn hiện mật khẩu">
                  <svg width="17" height="17" aria-hidden="true"><use href="#mayhen_forgot_password_icon_eye"/></svg>
                </button>
              </div>
              <div class="mayhen_reset_strength_bar">
                <div class="mayhen_reset_strength_step" id="mayhen_step_1"></div>
                <div class="mayhen_reset_strength_step" id="mayhen_step_2"></div>
                <div class="mayhen_reset_strength_step" id="mayhen_step_3"></div>
                <div class="mayhen_reset_strength_step" id="mayhen_step_4"></div>
              </div>
              @error('password')
                <span class="mayhen_forgot_password_field_error">{{ $message }}</span>
              @enderror
            </div>

            <div class="mayhen_forgot_password_field" style="margin-bottom: 18px;">
              <label class="mayhen_forgot_password_label" for="mayhen_password_confirm">Xác nhận mật khẩu mới</label>
              <div class="mayhen_forgot_password_input_wrap">
                <svg class="mayhen_forgot_password_input_icon" aria-hidden="true"><use href="#mayhen_forgot_password_icon_lock"/></svg>
                <input class="mayhen_forgot_password_input" 
                       id="mayhen_password_confirm" 
                       name="password_confirmation" 
                       type="password" 
                       required 
                       placeholder="Nhập lại mật khẩu mới..." 
                       autocomplete="new-password">
              </div>
            </div>

            <div class="mayhen_reset_requirements">
              <div class="mayhen_reset_req_item" id="req_len">
                <span class="mayhen_reset_req_icon">•</span> Tối thiểu 8 ký tự
              </div>
              <div class="mayhen_reset_req_item" id="req_char">
                <span class="mayhen_reset_req_icon">•</span> Kết hợp cả chữ cái và chữ số
              </div>
            </div>

            <button class="mayhen_forgot_password_primary" type="submit" id="mayhen_reset_submit">
              <span>Lưu mật khẩu mới &amp; Đăng nhập</span>
              <svg class="mayhen_forgot_password_primary_arrow" aria-hidden="true"><use href="#mayhen_forgot_password_icon_arrow"/></svg>
            </button>
          </form>

          <a class="mayhen_forgot_password_secondary_link" href="{{ route('login') }}"><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_back"/></svg>Quay lại đăng nhập</a>
        </section>
      </div>

      <footer class="mayhen_forgot_password_panel_bottom">
        <div class="mayhen_forgot_password_alert"><svg aria-hidden="true"><use href="#mayhen_forgot_password_icon_shield"/></svg><span><strong>Bảo mật liên kết:</strong> Ngay sau khi đổi thành công, liên kết này sẽ mất tác dụng vĩnh viễn.</span></div>
      </footer>
    </div>
  </section>
</main>

<script>
  (() => {
    const pwd = document.getElementById('mayhen_new_password');
    const toggle = document.getElementById('mayhen_toggle_pwd');
    const step1 = document.getElementById('mayhen_step_1');
    const step2 = document.getElementById('mayhen_step_2');
    const step3 = document.getElementById('mayhen_step_3');
    const step4 = document.getElementById('mayhen_step_4');
    const reqLen = document.getElementById('req_len');
    const reqChar = document.getElementById('req_char');

    if (toggle && pwd) {
      toggle.addEventListener('click', () => {
        const isPwd = pwd.type === 'password';
        pwd.type = isPwd ? 'text' : 'password';
      });
    }

    if (pwd) {
      pwd.addEventListener('input', () => {
        const val = pwd.value;
        const hasLen = val.length >= 8;
        const hasLetter = /[a-zA-Z]/.test(val);
        const hasNum = /[0-9]/.test(val);
        const hasSpecial = /[^a-zA-Z0-9]/.test(val);

        if (reqLen) reqLen.style.color = hasLen ? '#34765c' : '#7D6B66';
        if (reqChar) reqChar.style.color = (hasLetter && hasNum) ? '#34765c' : '#7D6B66';

        let score = 0;
        if (hasLen) score++;
        if (hasLetter && hasNum) score++;
        if (val.length >= 12) score++;
        if (hasSpecial) score++;

        const colors = ['#ab4552', '#d97706', '#3b82f6', '#34765c'];
        const activeColor = colors[Math.max(0, score - 1)] || '#E8DCD6';

        [step1, step2, step3, step4].forEach((step, idx) => {
          if (step) step.style.background = (idx < score) ? activeColor : 'transparent';
        });
      });
    }
  })();
</script>
</body>
</html>
