@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $brandNameUpper = \App\Models\PlatformSetting::getBrandNameUpper();
    $faviconUrl = \App\Models\PlatformSetting::getFaviconUrl();
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lại Mật Khẩu | {{ $brandName }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #FAF7F3; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color: #453738; -webkit-font-smoothing: antialiased; line-height: 1.65;">
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #FAF7F3; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 580px; background-color: #FFFFFF; border-radius: 20px; border: 1px solid #E6D4CA; overflow: hidden; box-shadow: 0 15px 45px rgba(75, 48, 43, 0.06);">
                    
                    <!-- Header Top Decor -->
                    <tr>
                        <td align="center" style="padding: 40px 30px 26px 30px; background: radial-gradient(ellipse at 50% 50%, #FFFDF9, #FBF3ED); border-bottom: 1px solid #F0E3DB;">
                            <div style="display: inline-block; width: 54px; height: 54px; line-height: 54px; border-radius: 50%; background-color: #FFFFFF; border: 1px solid #E6D4CA; box-shadow: 0 4px 14px rgba(128, 80, 82, 0.08); vertical-align: middle; text-align: center;">
                                <img src="{{ $faviconUrl }}" alt="{{ $brandName }}" width="28" height="28" style="display: inline-block; width: 28px; height: 28px; vertical-align: middle; border: 0; outline: none; margin-top: -3px;">
                            </div>
                            <h2 style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-size: 12px; letter-spacing: 0.18em; text-transform: uppercase; color: #B58E6F; margin: 16px 0 8px 0; font-weight: 700;">
                                {{ $brandNameUpper }} &bull; KHÔI PHỤC TÀI KHOẢN
                            </h2>
                            <h1 style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-size: 24px; line-height: 1.35; color: #684143; margin: 0; font-weight: 700; letter-spacing: normal;">
                                Đặt Lại Mật Khẩu
                            </h1>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 34px 38px 28px 38px;">
                            <p style="font-size: 15px; color: #453738; margin: 0 0 16px 0;">
                                Xin chào <strong>{{ $user->name }}</strong>,
                            </p>
                            <p style="font-size: 14.5px; line-height: 1.8; color: #665253; margin: 0 0 24px 0;">
                                Chúng mình nhận được yêu cầu đặt lại mật khẩu cho tài khoản <strong>{{ $brandName }}</strong> liên kết với email <span style="color: #805052; font-weight: 600;">{{ $user->email }}</span>. Bạn vui lòng nhấn vào nút bên dưới để tạo mật khẩu mới nhé:
                            </p>

                            <!-- Action Button -->
                            <div style="text-align: center; margin: 32px 0 20px 0;">
                                <a href="{{ $resetUrl }}" target="_blank" rel="noopener noreferrer"
                                   style="display: inline-block; background-color: #805052; color: #FFFFFF; font-size: 14.5px; font-weight: 600; text-decoration: none; padding: 14px 36px; border-radius: 12px; letter-spacing: 0.02em; box-shadow: 0 8px 22px rgba(128, 80, 82, 0.25);">
                                    Đặt Lại Mật Khẩu Ngay &rarr;
                                </a>
                            </div>

                            <p style="font-size: 13px; line-height: 1.6; color: #8A7A75; text-align: center; margin: 0 0 26px 0;">
                                <em>(Liên kết có hiệu lực trong vòng {{ $expireHours }} giờ. Nếu bạn không gửi yêu cầu này, bạn có thể yên tâm bỏ qua email).</em>
                            </p>

                            <!-- Fallback Link -->
                            <div style="border-top: 1px dashed #EEDFD7; padding-top: 20px; margin-top: 20px;">
                                <p style="font-size: 12px; line-height: 1.6; color: #9A8782; margin: 0; word-break: break-all;">
                                    Nếu nút bấm trên không hoạt động, bạn có thể sao chép và dán liên kết sau vào trình duyệt:<br>
                                    <a href="{{ $resetUrl }}" style="color: #805052; text-decoration: underline;">{{ $resetUrl }}</a>
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background-color: #FAF5F0; padding: 22px 30px; border-top: 1px solid #EFE4DC;">
                            <p style="font-size: 11.5px; color: #9A8782; margin: 0 0 6px 0;">
                                Email này được gửi tự động từ <strong>{{ $noreplyEmail ?? 'no-reply@mayhen.vn' }}</strong>.
                            </p>
                            <p style="font-size: 11.5px; color: #9A8782; margin: 0;">
                                &copy; {{ date('Y') }} {{ $brandName }} &bull; mayhen.vn
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
