@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $brandNameUpper = \App\Models\PlatformSetting::getBrandNameUpper();
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lại Mật Khẩu | {{ $brandName }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #FAF7F3; font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #453738; -webkit-font-smoothing: antialiased; line-height: 1.65;">
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #FAF7F3; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 580px; background-color: #FFFFFF; border-radius: 20px; border: 1px solid #E6D4CA; overflow: hidden; box-shadow: 0 15px 45px rgba(75, 48, 43, 0.06);">
                    
                    {{-- Header Top Decor --}}
                    <tr>
                        <td align="center" style="padding: 42px 30px 24px 30px; background: radial-gradient(ellipse at 50% 50%, #FFFDF9, #FBF3ED); border-bottom: 1px solid #F0E3DB;">
                            <div style="display: inline-block; width: 48px; height: 48px; line-height: 48px; border-radius: 50%; background-color: #F6ECE9; border: 1px solid #D8BCA5; color: #805052; font-size: 22px;">
                                💌
                            </div>
                            <h2 style="font-family: Georgia, 'Times New Roman', serif; font-size: 13px; letter-spacing: 0.22em; text-transform: uppercase; color: #B58E6F; margin: 18px 0 6px 0; font-weight: 700;">
                                {{ $brandNameUpper }} • GIỮ TRỌN KHOẢNH KHẮC
                            </h2>
                            <h1 style="font-family: Georgia, 'Times New Roman', serif; font-size: 27px; line-height: 1.25; color: #684143; margin: 0; font-weight: 400; letter-spacing: -0.02em;">
                                Khôi Phục Mật Khẩu
                            </h1>
                        </td>
                    </tr>

                    {{-- Body Content --}}
                    <tr>
                        <td style="padding: 32px 38px 24px 38px;">
                            <p style="font-size: 15px; color: #453738; margin: 0 0 16px 0;">
                                Xin chào <strong>{{ $user->name }}</strong>,
                            </p>
                            <p style="font-size: 14px; line-height: 1.8; color: #665253; margin: 0 0 20px 0;">
                                Chúng mình nhận được yêu cầu đặt lại mật khẩu cho tài khoản {{ $brandName }} liên kết với email <span style="color: #805052; font-weight: 600;">{{ $user->email }}</span>. Để tiếp tục chuẩn bị cho ngày trọng đại, bạn hãy nhấp vào nút bên dưới để tạo mật khẩu mới nhé:
                            </p>

                            {{-- Action Button --}}
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="{{ $resetUrl }}" target="_blank"
                                   style="display: inline-block; background-color: #805052; color: #FFFFFF; font-size: 14.5px; font-weight: 600; text-decoration: none; padding: 14px 34px; border-radius: 12px; letter-spacing: 0.02em; box-shadow: 0 8px 22px rgba(128, 80, 82, 0.25);">
                                    Đặt Lại Mật Khẩu Ngay &rarr;
                                </a>
                            </div>

                            {{-- Security Highlights Callout --}}
                            <div style="background-color: #FBF8F5; border: 1px solid #EBDED6; border-left: 4px solid #B58E6F; border-radius: 10px; padding: 16px 18px; margin: 26px 0;">
                                <p style="font-size: 12.5px; line-height: 1.75; color: #76615B; margin: 0 0 8px 0;">
                                    🔒 <strong>Thời hạn liên kết:</strong> Liên kết có hiệu lực trong vòng <strong>{{ $expireHours }} tiếng (48 giờ)</strong> kể từ thời điểm gửi.
                                </p>
                                <p style="font-size: 12.5px; line-height: 1.75; color: #76615B; margin: 0 0 8px 0;">
                                    ⚡ <strong>Dùng duy nhất một lần:</strong> Sau khi mật khẩu được đổi thành công, liên kết này sẽ tự động mất tác dụng để bảo vệ bạn tối đa.
                                </p>
                                <p style="font-size: 12.5px; line-height: 1.75; color: #76615B; margin: 0;">
                                    🛡️ <strong>Bảo mật:</strong> Nếu bạn không gửi yêu cầu này, vui lòng bỏ qua email. Mật khẩu hiện tại của bạn vẫn an toàn tuyệt đối.
                                </p>
                            </div>

                            {{-- Fallback Direct Link --}}
                            <p style="font-size: 12px; line-height: 1.6; color: #9A8782; margin: 24px 0 0 0; word-break: break-all;">
                                Nếu nút bấm trên không hoạt động, bạn có thể sao chép và dán liên kết sau vào trình duyệt:<br>
                                <a href="{{ $resetUrl }}" style="color: #805052; text-decoration: underline;">{{ $resetUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="background-color: #FAF5F0; padding: 22px 30px; border-top: 1px solid #EFE4DC;">
                            <p style="font-size: 11.5px; color: #9A8782; margin: 0 0 6px 0;">
                                Email này được gửi tự động từ <strong>{{ $noreplyEmail ?? 'no-reply@mayhen.vn' }}</strong>. Vui lòng không trả lời trực tiếp email này.
                            </p>
                            <p style="font-size: 11.5px; color: #9A8782; margin: 0;">
                                © {{ date('Y') }} {{ $brandName }} (mayhen.vn). All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
