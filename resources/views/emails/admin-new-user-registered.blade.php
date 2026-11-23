@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $brandNameUpper = \App\Models\PlatformSetting::getBrandNameUpper();
    $planLabel = strtoupper((string) ($user->current_plan_slug ?: 'premium'));
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Báo Người Dùng Mới Đăng Ký | {{ $brandName }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #FAF7F3; font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #453738; -webkit-font-smoothing: antialiased; line-height: 1.65;">
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #FAF7F3; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #FFFFFF; border-radius: 20px; border: 1px solid #E6D4CA; overflow: hidden; box-shadow: 0 15px 45px rgba(75, 48, 43, 0.06);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 38px 30px 24px 30px; background: radial-gradient(ellipse at 50% 50%, #FFFDF9, #FBF3ED); border-bottom: 1px solid #F0E3DB;">
                            <div style="display: inline-block; width: 48px; height: 48px; line-height: 48px; border-radius: 50%; background-color: #F6EFE9; border: 1px solid #D8C29D; color: #9C7A4A; font-size: 22px;">
                                &#10022;
                            </div>
                            <h2 style="font-family: Georgia, 'Times New Roman', serif; font-size: 12.5px; letter-spacing: 0.22em; text-transform: uppercase; color: #B58E6F; margin: 16px 0 6px 0; font-weight: 700;">
                                {{ $brandNameUpper }} &bull; THÔNG BÁO QUẢN TRỊ
                            </h2>
                            <h1 style="font-family: Georgia, 'Times New Roman', serif; font-size: 24px; line-height: 1.3; color: #684143; margin: 0; font-weight: 400; letter-spacing: -0.02em;">
                                Có Người Dùng Mới Đăng Ký Tài Khoản
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px 36px 24px 36px;">
                            <p style="font-size: 14.5px; color: #453738; margin: 0 0 14px 0;">
                                Kính gửi <strong>Ban Quản Trị {{ $brandName }}</strong>,
                            </p>
                            <p style="font-size: 14px; line-height: 1.75; color: #665253; margin: 0 0 22px 0;">
                                Hệ thống vừa ghi nhận một tài khoản khách hàng mới hoàn tất đăng ký thành công trên <strong>mayhen.vn</strong> và đã được kích hoạt mặc định <strong>Gói {{ $planLabel }}</strong>.
                            </p>

                            <!-- User Info Table -->
                            <div style="background-color: #FCFBF9; border: 1px solid #EBDED6; border-radius: 12px; overflow: hidden; margin-bottom: 24px;">
                                <div style="padding: 12px 18px; background-color: #F6EFE9; border-bottom: 1px solid #EBDED6; font-size: 12px; font-weight: 700; color: #684143; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Thông Tin Tài Khoản Vừa Đăng Ký
                                </div>
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size: 13.5px; line-height: 1.6;">
                                    <tr>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #8A7A75; width: 165px;">Họ và tên:</td>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #332B2B; font-weight: 600;">{{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #8A7A75;">Địa chỉ Email:</td>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #805052; font-weight: 600;">{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #8A7A75;">Số điện thoại:</td>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #332B2B;">{{ $user->phone ?: 'Chưa cung cấp' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #8A7A75;">Phương thức đăng ký:</td>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #332B2B;">{{ $registrationMethodLabel }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #8A7A75;">Gói mặc định:</td>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #2E7D32; font-weight: 600;">Gói {{ $planLabel }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #8A7A75;">Thời gian đăng ký:</td>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #332B2B;">{{ $registeredAt }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 18px; color: #8A7A75;">Địa chỉ IP:</td>
                                        <td style="padding: 10px 18px; color: #332B2B; font-family: monospace;">{{ $ipAddress }}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Action Button -->
                            <div style="text-align: center; margin: 24px 0 8px 0;">
                                <a href="{{ route('admin.users.index') }}" style="display: inline-block; padding: 12px 28px; background-color: #684143; color: #FFFFFF; text-decoration: none; border-radius: 999px; font-size: 13.5px; font-weight: 600; letter-spacing: 0.02em;">
                                    Xem Danh Sách Người Dùng Trong Admin &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 22px 30px; background-color: #FAF7F3; border-top: 1px solid #EBDED6; font-size: 12px; color: #9A8782; line-height: 1.7;">
                            <div style="font-weight: 600; color: #684143; margin-bottom: 4px;">
                                {{ $brandName }} &bull; Thông Báo Tự Động Từ Hệ Thống
                            </div>
                            <div>
                                Email này được gửi tự động từ địa chỉ <strong>{{ $noreplyEmail }}</strong> qua hàng đợi (Queue). Vui lòng không trả lời trực tiếp thư này.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
