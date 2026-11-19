@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $brandNameUpper = \App\Models\PlatformSetting::getBrandNameUpper();
    $host = $smtpDetails['host'] ?? 'smtp.tino.vn';
    $port = $smtpDetails['port'] ?? 587;
    $encryption = strtoupper($smtpDetails['encryption'] ?? 'TLS');
    $fromAddress = $smtpDetails['from_address'] ?? 'no-reply@mayhen.vn';
    $fromName = $smtpDetails['from_name'] ?? $brandName;
    $sentAt = $smtpDetails['sent_at'] ?? now()->format('d/m/Y H:i:s');
    $clientIp = $smtpDetails['client_ip'] ?? '127.0.0.1';
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm Tra SMTP | {{ $brandName }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #FAF7F3; font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #453738; -webkit-font-smoothing: antialiased; line-height: 1.65;">
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #FAF7F3; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #FFFFFF; border-radius: 20px; border: 1px solid #E6D4CA; overflow: hidden; box-shadow: 0 15px 45px rgba(75, 48, 43, 0.06);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 42px 30px 26px 30px; background: radial-gradient(ellipse at 50% 50%, #FFFDF9, #FBF3ED); border-bottom: 1px solid #F0E3DB;">
                            <div style="display: inline-block; width: 50px; height: 50px; line-height: 50px; border-radius: 50%; background-color: #E8F5E9; border: 1px solid #A5D6A7; color: #2E7D32; font-size: 24px;">
                                ✓
                            </div>
                            <h2 style="font-family: Georgia, 'Times New Roman', serif; font-size: 13px; letter-spacing: 0.22em; text-transform: uppercase; color: #B58E6F; margin: 18px 0 6px 0; font-weight: 700;">
                                {{ $brandNameUpper }} &bull; SAFE PLATFORM COCKPIT
                            </h2>
                            <h1 style="font-family: Georgia, 'Times New Roman', serif; font-size: 25px; line-height: 1.3; color: #684143; margin: 0; font-weight: 400; letter-spacing: -0.02em;">
                                Xác Nhận Máy Chủ SMTP Hoạt Động Thành Công
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 34px 38px 24px 38px;">
                            <p style="font-size: 15px; color: #453738; margin: 0 0 14px 0;">
                                Kính gửi <strong>{{ $adminName ?? 'Ban Quản Trị' }}</strong>,
                            </p>
                            <p style="font-size: 14px; line-height: 1.8; color: #665253; margin: 0 0 22px 0;">
                                Email này được gửi trực tiếp từ trung tâm cấu hình nền tảng an toàn của <strong>{{ $brandName }}</strong>. Điều này chứng minh rằng thông số kết nối máy chủ gửi thư (SMTP Server) đã được thiết lập chính xác, chứng chỉ bảo mật hợp lệ và sẵn sàng phục vụ các luồng giao dịch trực tuyến.
                            </p>

                            <!-- Badge Success -->
                            <div style="background-color: #F1F8E9; border: 1px solid #C5E1A5; border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; display: flex; align-items: center;">
                                <div style="font-size: 13.5px; color: #33691E; font-weight: 600;">
                                    🟢 Trạng thái: Kênh gửi thư SMTP đã được kết nối &amp; phát thư thành công
                                </div>
                            </div>

                            <!-- Spec Table -->
                            <div style="background-color: #FCFBF9; border: 1px solid #EBDED6; border-radius: 12px; overflow: hidden; margin-bottom: 26px;">
                                <div style="padding: 12px 18px; background-color: #F6EFE9; border-bottom: 1px solid #EBDED6; font-size: 12.5px; font-weight: 700; color: #684143; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Chi Tiết Thông Số Kết Nối Kỹ Thuật
                                </div>
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size: 13px; line-height: 1.6;">
                                    <tr>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #8A7A75; width: 160px;">Máy chủ (Host):</td>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #332B2B; font-weight: 600; font-family: monospace;">{{ $host }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #8A7A75;">Cổng kết nối (Port):</td>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #332B2B; font-weight: 600; font-family: monospace;">{{ $port }} ({{ $encryption }})</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #8A7A75;">Người gửi (From):</td>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #332B2B;">{{ $fromName }} &lt;{{ $fromAddress }}&gt;</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #8A7A75;">Người nhận kiểm thử:</td>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #805052; font-weight: 600;">{{ $recipientEmail }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #8A7A75;">Thời gian gửi đi:</td>
                                        <td style="padding: 10px 18px; border-bottom: 1px solid #F0E6DE; color: #332B2B;">{{ $sentAt }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 18px; color: #8A7A75;">Địa chỉ IP kích hoạt:</td>
                                        <td style="padding: 10px 18px; color: #332B2B; font-family: monospace;">{{ $clientIp }}</td>
                                    </tr>
                                </table>
                            </div>

                            <div style="background-color: #FAF5F2; border: 1px solid #EAD8CE; border-left: 4px solid #805052; border-radius: 8px; padding: 14px 16px; margin: 20px 0;">
                                <p style="font-size: 12.5px; line-height: 1.7; color: #6E5354; margin: 0;">
                                    💡 <strong>Khuyến nghị vận hành:</strong> Hãy kiểm tra xem email này có rơi vào mục Spam/Rác không. Nếu rơi vào Spam, vui lòng kiểm tra lại bản ghi <code>SPF</code>, <code>DKIM</code> và <code>DMARC</code> trên hệ thống quản lý DNS tên miền <code>mayhen.vn</code> để tối ưu tỷ lệ vào Hộp thư chính (Inbox).
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 24px 30px; background-color: #FAF7F3; border-top: 1px solid #EBDED6; font-size: 12px; color: #9A8782; line-height: 1.7;">
                            <div style="font-weight: 600; color: #684143; margin-bottom: 4px;">
                                {{ $brandName }} &bull; Nền Tảng Thiệp Cưới Trực Tuyến Cao Cấp
                            </div>
                            <div>
                                Safe Cockpit System &bull; Phát hành bởi Hệ thống Quản trị Viên {{ $brandName }}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
