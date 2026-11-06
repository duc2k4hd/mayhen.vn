<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#fbf8f4">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Đăng nhập vào không gian quản trị Mây Hẹn (mayhen.vn)">
    <title>Đăng nhập Quản Trị | Mây Hẹn</title>
    @include('client.components.favicon')

    <!-- Admin Isolated Login Stylesheet -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/login.css') }}">
</head>
<body class="mayhen_admin_login_body">
    <main class="mayhen_admin_login_page">
        <div class="mayhen_admin_login_outer_line mayhen_admin_login_outer_line_top" aria-hidden="true"></div>
        <div class="mayhen_admin_login_outer_line mayhen_admin_login_outer_line_bottom" aria-hidden="true"></div>

        <div class="mayhen_admin_login_shell">
            {{-- Cột thiệp cưới nghệ thuật bên trái --}}
            <section class="mayhen_admin_login_story" aria-label="Lời chào Mây Hẹn">
                <header class="mayhen_admin_login_story_header">
                    <div class="mayhen_admin_login_brand">
                        <svg class="mayhen_admin_login_brand_mark" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                            <circle cx="24" cy="24" r="21" stroke="currentColor" stroke-opacity=".48" stroke-width=".8" />
                            <circle cx="24" cy="24" r="17.5" stroke="currentColor" stroke-opacity=".28" stroke-width=".7" />
                            <path d="M10 27c6-1 7-12 14-12s8 11 14 12" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
                            <path d="M14 24c4 7 7 9 10 9s6-2 10-9" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
                            <path d="M24 12v5m-2.5-2.5h5" stroke="currentColor" stroke-width=".9" stroke-linecap="round" />
                            <circle cx="24" cy="35" r="1.2" fill="currentColor" />
                        </svg>
                        <div>
                            <div class="mayhen_admin_login_brand_name">MÂY HẸN</div>
                            <span class="mayhen_admin_login_brand_subtitle">WEDDING ATELIER</span>
                        </div>
                    </div>
                    <div class="mayhen_admin_login_story_header_note">Crafted with love ♡</div>
                </header>

                <div class="mayhen_admin_login_story_main">
                    <div class="mayhen_admin_login_eyebrow">Từng khoảnh khắc đáng nhớ</div>
                    <h1 class="mayhen_admin_login_story_title">Nơi những lời hẹn<br>hóa thành <em>kỷ niệm.</em></h1>
                    <p class="mayhen_admin_login_story_description">
                        Một góc nhỏ để chăm chút từng chi tiết, lưu giữ cảm xúc và tạo nên những câu chuyện thật đẹp.
                    </p>
                    <div class="mayhen_admin_login_art" aria-hidden="true">
                        <svg class="mayhen_admin_login_art_svg" viewBox="0 0 430 270" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <ellipse cx="215" cy="133" rx="80" ry="95" stroke="currentColor" stroke-opacity=".50" stroke-width=".8" />
                            <ellipse cx="215" cy="133" rx="72" ry="87" stroke="currentColor" stroke-opacity=".29" stroke-width=".7" />
                            <path d="M196 133h38m-19-19v38" stroke="currentColor" stroke-opacity=".23" stroke-width=".7" />
                            <text x="215" y="145" fill="#805052" font-family="Georgia, serif" font-style="italic" font-size="39" text-anchor="middle">M</text>
                            <text x="215" y="167" fill="#9e7a68" font-family="Georgia, serif" font-style="italic" font-size="15" text-anchor="middle">&amp; H</text>
                            <path d="M103 212c-1-37 27-55 33-88 5-29-10-49-17-66" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                            <path d="M121 68c-19-3-27-14-24-30 17 3 26 12 24 30ZM129 94c-19-2-30-12-30-26 20 0 31 9 30 26ZM132 112c17-17 30-17 40-11-11 16-26 22-40 11ZM121 144c-21-9-32-7-43 6 18 10 32 9 43-6ZM106 184c17-17 29-17 39-11-9 17-24 22-39 11Z" fill="#c7a98c" fill-opacity=".20" stroke="currentColor" stroke-width="1" stroke-linejoin="round" />
                            <path d="M327 212c1-37-27-55-33-88-5-29 10-49 17-66" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                            <path d="M309 68c19-3 27-14 24-30-17 3-26 12-24 30ZM301 94c19-2 30-12 30-26-20 0-31 9-30 26ZM298 112c-17-17-30-17-40-11 11 16 26 22 40 11ZM309 144c21-9 32-7 43 6-18 10-32 9-43-6ZM324 184c-17-17-29-17-39-11 9 17 24 22 39 11Z" fill="#c7a98c" fill-opacity=".20" stroke="currentColor" stroke-width="1" stroke-linejoin="round" />
                            <path d="M93 227c28 12 47 8 67-2m177 2c-28 12-47 8-67-2" stroke="currentColor" stroke-opacity=".35" stroke-width=".8" stroke-linecap="round" />
                            <path d="M215 24l3 4-3 4-3-4 3-4Zm0 213 3 4-3 4-3-4 3-4Z" fill="currentColor" fill-opacity=".6" />
                            <circle cx="93" cy="227" r="2" fill="currentColor" fill-opacity=".4" />
                            <circle cx="337" cy="227" r="2" fill="currentColor" fill-opacity=".4" />
                        </svg>
                    </div>
                </div>

                <footer class="mayhen_admin_login_story_footer">
                    <span class="mayhen_admin_login_story_footer_symbol">✦</span>
                    <span class="mayhen_admin_login_story_footer_line"></span>
                    <span>Made for moments that matter</span>
                    <span class="mayhen_admin_login_story_footer_line"></span>
                    <span class="mayhen_admin_login_story_footer_symbol">✦</span>
                </footer>
            </section>

            {{-- Cột đăng nhập quản trị bên phải --}}
            <section class="mayhen_admin_login_panel" aria-labelledby="mayhen_admin_login_title">
                <header class="mayhen_admin_login_panel_header">
                    <span class="mayhen_admin_login_panel_header_dot" aria-hidden="true"></span>
                    <span>Không gian quản trị</span>
                </header>

                <div class="mayhen_admin_login_form_area">
                    <div class="mayhen_admin_login_small_ornament" aria-hidden="true">
                        <svg viewBox="0 0 52 42" fill="none">
                            <path d="M26 38V10m0 16C15 25 12 19 13 14c9-1 13 4 13 12Zm0-8C36 16 39 10 37 5c-9 0-12 5-11 13Zm0 16c12-1 16-8 15-13-11-1-15 5-15 13Z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round" />
                            <path d="M19 39h14" stroke="currentColor" stroke-width=".8" />
                            <path d="M26 2v3" stroke="currentColor" stroke-width="1.1" stroke-linecap="round" />
                        </svg>
                    </div>
                    <p class="mayhen_admin_login_welcome">Welcome back, dear</p>
                    <h2 class="mayhen_admin_login_title" id="mayhen_admin_login_title">Đăng nhập</h2>
                    <p class="mayhen_admin_login_subtitle">Trở lại không gian nơi mọi chi tiết đều được nâng niu.</p>

                    <form class="mayhen_admin_login_form" id="mayhen_admin_login_form" method="POST" action="{{ route('admin.login.submit') }}" novalidate>
                        @csrf

                        {{-- Trường Email --}}
                        <div class="mayhen_admin_login_field">
                            <label class="mayhen_admin_login_field_label" for="mayhen_admin_login_email">Email quản trị</label>
                            <div class="mayhen_admin_login_input_wrap">
                                <svg class="mayhen_admin_login_field_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="14" rx="2" /><path d="m3.5 6 8.5 7 8.5-7" />
                                </svg>
                                <input class="mayhen_admin_login_input" 
                                       id="mayhen_admin_login_email" 
                                       name="email" 
                                       type="email" 
                                       inputmode="email" 
                                       autocomplete="username" 
                                       autocapitalize="none" 
                                       spellcheck="false" 
                                       placeholder="gmail@gmail.com" 
                                       value="{{ old('email') }}" 
                                       required 
                                       aria-describedby="mayhen_admin_login_email_error" 
                                       @error('email') aria-invalid="true" @enderror />
                            </div>
                            <div class="mayhen_admin_login_field_error" id="mayhen_admin_login_email_error" aria-live="polite">
                                @error('email') {{ $message }} @enderror
                            </div>
                        </div>

                        {{-- Trường Mật Khẩu --}}
                        <div class="mayhen_admin_login_field">
                            <label class="mayhen_admin_login_field_label" for="mayhen_admin_login_password">Mật khẩu</label>
                            <div class="mayhen_admin_login_input_wrap">
                                <svg class="mayhen_admin_login_field_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="5" y="10" width="14" height="11" rx="2" /><path d="M8 10V7a4 4 0 0 1 8 0v3" /><path d="M12 14v3" />
                                </svg>
                                <input class="mayhen_admin_login_input" 
                                       id="mayhen_admin_login_password" 
                                       name="password" 
                                       type="password" 
                                       autocomplete="current-password" 
                                       placeholder="Nhập mật khẩu quản trị" 
                                       required 
                                       aria-describedby="mayhen_admin_login_password_error" 
                                       @error('password') aria-invalid="true" @enderror />
                                <button class="mayhen_admin_login_password_toggle" id="mayhen_admin_login_password_toggle" type="button" aria-label="Hiện mật khẩu" aria-pressed="false" data-visible="false">
                                    <svg class="mayhen_admin_login_password_icon_visible" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="mayhen_admin_login_password_icon_hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.6 6.1c.5-.1 1-.1 1.4-.1 6.4 0 10 6 10 6a17 17 0 0 1-3.2 3.5M6 8.2A16.5 16.5 0 0 0 2 12s3.6 6 10 6c1.3 0 2.5-.2 3.6-.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2M3 3l18 18"/></svg>
                                </button>
                            </div>
                            <div class="mayhen_admin_login_field_error" id="mayhen_admin_login_password_error" aria-live="polite">
                                @error('password') {{ $message }} @enderror
                            </div>
                        </div>

                        {{-- Tùy chọn Ghi nhớ & Quên mật khẩu --}}
                        <div class="mayhen_admin_login_options">
                            <label class="mayhen_admin_login_remember" for="mayhen_admin_login_remember">
                                <input type="checkbox" id="mayhen_admin_login_remember" name="remember" value="1" />
                                <span>Ghi nhớ email</span>
                            </label>
                            <span class="mayhen_admin_login_forgot" style="cursor: default; color: #a38879;">Bảo mật</span>
                        </div>

                        {{-- Nút Đăng nhập --}}
                        <button class="mayhen_admin_login_submit" id="mayhen_admin_login_submit" type="submit">
                            <span id="mayhen_admin_login_submit_label">Vào không gian quản trị</span>
                            <svg class="mayhen_admin_login_submit_arrow" id="mayhen_admin_login_submit_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15m-6-6 6 6-6 6"/></svg>
                        </button>

                        {{-- Thông báo trạng thái --}}
                        <p class="mayhen_admin_login_message" id="mayhen_admin_login_message" role="status" aria-live="polite">
                            @if (session('success'))
                                <span style="color: var(--mayhen-admin-success);">{{ session('success') }}</span>
                            @endif
                        </p>
                    </form>

                    <div class="mayhen_admin_login_quote"><span class="mayhen_admin_login_quote_text">Every love story deserves beautiful details.</span></div>
                </div>

                <footer class="mayhen_admin_login_panel_footer">
                    <span>© <span id="mayhen_admin_login_year">{{ date('Y') }}</span> Mây Hẹn (mayhen.vn)</span>
                    <span class="mayhen_admin_login_panel_footer_sep" aria-hidden="true">·</span>
                    <span>Chăm chút từng lời hẹn</span>
                </footer>
            </section>
        </div>
    </main>

    <script>
        (function () {
            "use strict";

            const mayhenAdminLoginConfig = {
                loginApiUrl: "{{ route('admin.login.submit') }}",
                redirectUrl: "{{ route('admin.dashboard') }}"
            };

            const form = document.getElementById("mayhen_admin_login_form");
            const email = document.getElementById("mayhen_admin_login_email");
            const password = document.getElementById("mayhen_admin_login_password");
            const remember = document.getElementById("mayhen_admin_login_remember");
            const toggle = document.getElementById("mayhen_admin_login_password_toggle");
            const submit = document.getElementById("mayhen_admin_login_submit");
            const submitLabel = document.getElementById("mayhen_admin_login_submit_label");
            const message = document.getElementById("mayhen_admin_login_message");
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
            const storageKey = "mayhen_admin_login_remembered_email";

            function setMessage(value, type) {
                message.textContent = value;
                if (type) message.dataset.type = type;
                else delete message.dataset.type;
            }

            function setError(input, errorId, text) {
                input.setAttribute("aria-invalid", text ? "true" : "false");
                const errEl = document.getElementById(errorId);
                if (errEl) errEl.textContent = text;
            }

            function validate() {
                const emailValue = email.value.trim();
                const passwordValue = password.value;
                let firstInvalid = null;

                if (!emailValue) {
                    setError(email, "mayhen_admin_login_email_error", "Vui lòng nhập email quản trị.");
                    firstInvalid = email;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue)) {
                    setError(email, "mayhen_admin_login_email_error", "Email chưa đúng định dạng.");
                    firstInvalid = email;
                } else {
                    setError(email, "mayhen_admin_login_email_error", "");
                }

                if (!passwordValue) {
                    setError(password, "mayhen_admin_login_password_error", "Vui lòng nhập mật khẩu.");
                    if (!firstInvalid) firstInvalid = password;
                } else {
                    setError(password, "mayhen_admin_login_password_error", "");
                }

                if (firstInvalid) firstInvalid.focus();
                return !firstInvalid;
            }

            function setLoading(value) {
                submit.disabled = value;
                submit.dataset.loading = String(value);
                submitLabel.textContent = value ? "Đang xác thực..." : "Vào không gian quản trị";
                const iconEl = document.getElementById("mayhen_admin_login_submit_icon");
                if (iconEl) {
                    iconEl.innerHTML = value
                        ? '<path d="M12 3a9 9 0 1 1-9 9" />'
                        : '<path d="M4 12h15m-6-6 6 6-6 6" />';
                }
            }

            // Ghi nhớ email trong localStorage
            try {
                const savedEmail = window.localStorage.getItem(storageKey);
                if (savedEmail && !email.value) { 
                    email.value = savedEmail; 
                    remember.checked = true; 
                }
            } catch (_) {}

            email.addEventListener("input", function () {
                setError(email, "mayhen_admin_login_email_error", "");
                setMessage("", "");
            });

            password.addEventListener("input", function () {
                setError(password, "mayhen_admin_login_password_error", "");
                setMessage("", "");
            });

            // Ẩn / hiện mật khẩu
            if (toggle) {
                toggle.addEventListener("click", function () {
                    const willShow = password.type === "password";
                    password.type = willShow ? "text" : "password";
                    toggle.dataset.visible = String(willShow);
                    toggle.setAttribute("aria-pressed", String(willShow));
                    toggle.setAttribute("aria-label", willShow ? "Ẩn mật khẩu" : "Hiện mật khẩu");
                    password.focus({ preventScroll: true });
                });
            }

            // Xử lý gửi biểu mẫu AJAX
            form.addEventListener("submit", async function (event) {
                event.preventDefault();
                setMessage("", "");
                if (!validate()) return;

                try {
                    if (remember.checked) {
                        window.localStorage.setItem(storageKey, email.value.trim());
                    } else {
                        window.localStorage.removeItem(storageKey);
                    }
                } catch (_) {}

                setLoading(true);
                try {
                    const response = await fetch(mayhenAdminLoginConfig.loginApiUrl, {
                        method: "POST",
                        credentials: "same-origin",
                        headers: { 
                            "Content-Type": "application/json", 
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": csrfToken
                        },
                        body: JSON.stringify({ 
                            email: email.value.trim(), 
                            password: password.value,
                            remember: remember.checked
                        })
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        let errorText = data.message || "Đăng nhập chưa thành công. Vui lòng kiểm tra lại.";
                        if (data.errors) {
                            const firstErr = Object.values(data.errors)[0];
                            if (Array.isArray(firstErr) && firstErr[0]) errorText = firstErr[0];
                        }
                        throw new Error(errorText);
                    }

                    setMessage("Xác thực thành công! Đang chuyển hướng...", "success");
                    password.value = "";
                    window.location.assign(data.redirect || mayhenAdminLoginConfig.redirectUrl);
                } catch (error) {
                    setMessage(error && error.message ? error.message : "Đăng nhập thất bại. Vui lòng thử lại.", "error");
                } finally {
                    setLoading(false);
                }
            });
        })();
    </script>
</body>
</html>
