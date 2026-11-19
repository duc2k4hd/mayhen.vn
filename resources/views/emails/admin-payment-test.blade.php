@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $brandNameUpper = \App\Models\PlatformSetting::getBrandNameUpper();
    $orderCode = $paymentDetails['order_code'] ?? 'MH-TEST-ORD8899';
    $planName = $paymentDetails['plan_name'] ?? 'Gói Thiết Kế Thiệp Cưới Vĩnh Cửu (Lưu Trữ Trọn Đời)';
    $amountFormatted = $paymentDetails['amount_formatted'] ?? '399.000 đ';
    $bankName = $paymentDetails['bank_name'] ?? 'Ngân hàng TMCP Quân Đội (MBBank)';
    $bankAccount = $paymentDetails['bank_account'] ?? '0988888888';
    $bankAccountName = $paymentDetails['bank_account_name'] ?? 'CONG TY TNHH MAY HEN ATELIER';
    $memo = $paymentDetails['memo'] ?? 'MH TEST 9988';
    $paidAt = $paymentDetails['paid_at'] ?? now()->format('d/m/Y H:i:s');
    $qrDataUri = $paymentDetails['qr_data_uri'] ?? null;
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Nhận Thanh Toán Mẫu | {{ $brandName }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #FAF7F3; font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #453738; -webkit-font-smoothing: antialiased; line-height: 1.65;">
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #FAF7F3; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 620px; background-color: #FFFFFF; border-radius: 20px; border: 1px solid #E6D4CA; overflow: hidden; box-shadow: 0 15px 45px rgba(75, 48, 43, 0.06);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 42px 30px 26px 30px; background: radial-gradient(ellipse at 50% 50%, #FFFDF9, #FBF3ED); border-bottom: 1px solid #F0E3DB;">
                            <div style="display: inline-block; width: 50px; height: 50px; line-height: 50px; border-radius: 50%; background-color: #F6ECE9; border: 1px solid #D8BCA5; color: #805052; font-size: 24px;">
                                ⚜️
                            </div>
                            <h2 style="font-family: Georgia, 'Times New Roman', serif; font-size: 13px; letter-spacing: 0.22em; text-transform: uppercase; color: #B58E6F; margin: 18px 0 6px 0; font-weight: 700;">
                                {{ $brandNameUpper }} &bull; BIÊN NHẬN THANH TOÁN
                            </h2>
                            <h1 style="font-family: Georgia, 'Times New Roman', serif; font-size: 25px; line-height: 1.3; color: #684143; margin: 0; font-weight: 400; letter-spacing: -0.02em;">
                                Kích Hoạt Gói Thiệp Cưới Thành Công
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 34px 38px 24px 38px;">
                            <p style="font-size: 15px; color: #453738; margin: 0 0 14px 0;">
                                Xin chào <strong>{{ $adminName ?? 'Cặp Đôi Mây Hẹn' }}</strong>,
                            </p>
                            <p style="font-size: 14px; line-height: 1.8; color: #665253; margin: 0 0 20px 0;">
                                {{ $brandName }} xin trân trọng thông báo giao dịch chuyển khoản VietQR cho đơn hàng <strong style="color: #805052;">#{{ $orderCode }}</strong> của bạn đã được đối soát thành công và toàn bộ đặc quyền thiệp cưới cao cấp đã được mở khóa.
                            </p>

                            <!-- Alert Admin Test Note -->
                            <div style="background-color: #FFF9C4; border: 1px solid #FFF176; border-radius: 10px; padding: 12px 16px; margin-bottom: 24px;">
                                <div style="font-size: 12px; color: #F57F17; line-height: 1.6; font-weight: 500;">
                                    📌 <strong>Ghi chú kiểm thử Admin:</strong> Đây là email thanh toán mẫu phục vụ kiểm tra đường truyền và thiết kế thư thông báo của nền tảng trước khi phát hành cho khách hàng thực tế.
                                </div>
                            </div>

                            <!-- Invoice Card -->
                            <div style="background-color: #FCFBF9; border: 1px solid #EBDED6; border-radius: 14px; overflow: hidden; margin-bottom: 26px;">
                                <div style="padding: 14px 20px; background-color: #F6EFE9; border-bottom: 1px solid #EBDED6; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 12.5px; font-weight: 700; color: #684143; text-transform: uppercase; letter-spacing: 0.05em;">Chi Tiết Giao Dịch #{{ $orderCode }}</span>
                                    <span style="background-color: #E8F5E9; color: #2E7D32; font-size: 11.5px; font-weight: 700; padding: 3px 8px; border-radius: 4px; border: 1px solid #A5D6A7;">ĐÃ THANH TOÁN</span>
                                </div>
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size: 13.5px; line-height: 1.6;">
                                    <tr>
                                        <td style="padding: 12px 20px; border-bottom: 1px solid #F0E6DE; color: #8A7A75; width: 150px;">Gói dịch vụ:</td>
                                        <td style="padding: 12px 20px; border-bottom: 1px solid #F0E6DE; color: #332B2B; font-weight: 600;">{{ $planName }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 20px; border-bottom: 1px solid #F0E6DE; color: #8A7A75;">Số tiền thanh toán:</td>
                                        <td style="padding: 12px 20px; border-bottom: 1px solid #F0E6DE; color: #805052; font-weight: 700; font-size: 16px;">{{ $amountFormatted }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 20px; border-bottom: 1px solid #F0E6DE; color: #8A7A75;">Cổng thanh toán:</td>
                                        <td style="padding: 12px 20px; border-bottom: 1px solid #F0E6DE; color: #332B2B;">Chuyển khoản VietQR &bull; Napas 24/7 (SePay)</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 20px; border-bottom: 1px solid #F0E6DE; color: #8A7A75;">Tài khoản nhận:</td>
                                        <td style="padding: 12px 20px; border-bottom: 1px solid #F0E6DE; color: #332B2B;">
                                            {{ $bankName }}<br>
                                            Số TK: <span style="font-family: monospace; font-weight: 600;">{{ $bankAccount }}</span> &bull; Chủ TK: <span style="font-weight: 600;">{{ $bankAccountName }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 20px; border-bottom: 1px solid #F0E6DE; color: #8A7A75;">Cú pháp chuyển khoản:</td>
                                        <td style="padding: 12px 20px; border-bottom: 1px solid #F0E6DE; color: #332B2B; font-family: monospace; font-weight: 600;">{{ $memo }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 20px; color: #8A7A75;">Thời gian ghi nhận:</td>
                                        <td style="padding: 12px 20px; color: #332B2B;">{{ $paidAt }}</td>
                                    </tr>
                                </table>
                            </div>

                            @if(!empty($qrDataUri))
                                <div style="text-align: center; margin: 24px 0; padding: 18px; background-color: #FAF8F5; border: 1px dashed #D6C2B4; border-radius: 12px;">
                                    <div style="font-size: 12.5px; font-weight: 600; color: #684143; margin-bottom: 10px;">
                                        Mã VietQR Mẫu Đã Dùng Trong Giao Dịch
                                    </div>
                                    <img src="{{ $qrDataUri }}" alt="VietQR Sample" style="width: 170px; height: 170px; border-radius: 8px; border: 1px solid #EBDED6; background: #FFFFFF; padding: 6px;">
                                    <div style="font-size: 11.5px; color: #8A7A75; margin-top: 8px;">
                                        Quét chuyển khoản siêu tốc qua hơn 50+ ứng dụng ngân hàng di động
                                    </div>
                                </div>
                            @endif

                            <!-- Action Button -->
                            <div style="text-align: center; margin: 30px 0 20px 0;">
                                <a href="{{ url('/studio') }}" target="_blank" rel="noopener noreferrer"
                                   style="display: inline-block; background-color: #805052; color: #FFFFFF; font-size: 14.5px; font-weight: 600; text-decoration: none; padding: 14px 34px; border-radius: 12px; letter-spacing: 0.02em; box-shadow: 0 8px 22px rgba(128, 80, 82, 0.25);">
                                    Vào Studio Chỉnh Sửa Thiệp Ngay &rarr;
                                </a>
                            </div>

                            <p style="font-size: 13px; color: #8A7A75; text-align: center; margin: 0;">
                                Cần hỗ trợ khẩn cấp? Vui lòng liên hệ Hotline CSKH: <strong style="color: #684143;">{{ \App\Models\PlatformSetting::getValue(\App\Models\PlatformSetting::GROUP_BRAND_SEO, 'hotline', '0988.776.xxx') }}</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 24px 30px; background-color: #FAF7F3; border-top: 1px solid #EBDED6; font-size: 12px; color: #9A8782; line-height: 1.7;">
                            <div style="font-weight: 600; color: #684143; margin-bottom: 4px;">
                                {{ $brandName }} &bull; Nền Tảng Thiệp Cưới Trực Tuyến Cao Cấp
                            </div>
                            <div>
                                Hóa đơn điện tử tự động &bull; Bản quyền thuộc về {{ $brandName }}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
