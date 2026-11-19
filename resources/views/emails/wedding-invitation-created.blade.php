@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $brandNameUpper = \App\Models\PlatformSetting::getBrandNameUpper();
    $faviconUrl = \App\Models\PlatformSetting::getFaviconUrl();

    $groom = trim((string) ($wedding->groom_name ?? ''));
    $bride = trim((string) ($wedding->bride_name ?? ''));
    $coupleDisplay = $groom !== '' && $bride !== '' ? "{$groom} & {$bride}" : ($groom ?: ($bride ?: 'Hai Bạn'));
    $templateName = $template->name ?? 'Thiết Kế Độc Bản Mây Hẹn';
    $venue = trim((string) ($wedding->venue_name ?? ''));
    $address = trim((string) ($wedding->venue_address ?? ''));
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chúc Mừng Thiệp Cưới Của {{ $coupleDisplay }} Đã Sẵn Sàng | {{ $brandName }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #FAF7F3; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color: #453738; -webkit-font-smoothing: antialiased; line-height: 1.65;">
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #FAF7F3; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 620px; background-color: #FFFFFF; border-radius: 20px; border: 1px solid #E8DDD5; overflow: hidden; box-shadow: 0 15px 45px rgba(75, 48, 43, 0.05);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 42px 30px 28px 30px; background: radial-gradient(ellipse at 50% 50%, #FFFDF9, #FAF3ED); border-bottom: 1px solid #F0E3DB;">
                            <div style="display: inline-block; width: 56px; height: 56px; line-height: 56px; border-radius: 50%; background-color: #FFFFFF; border: 1px solid #E6D4CA; box-shadow: 0 4px 14px rgba(128, 80, 82, 0.08); vertical-align: middle; text-align: center;">
                                <img src="{{ $faviconUrl }}" alt="{{ $brandName }}" width="28" height="28" style="display: inline-block; width: 28px; height: 28px; vertical-align: middle; border: 0; outline: none; margin-top: -3px;">
                            </div>
                            <h2 style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-size: 12px; letter-spacing: 0.2em; text-transform: uppercase; color: #B58E6F; margin: 16px 0 8px 0; font-weight: 700;">
                                {{ $brandNameUpper }} &bull; TÁC PHẨM THIỆP CƯỚI ĐÃ SẴN SÀNG
                            </h2>
                            <h1 style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-size: 24px; line-height: 1.35; color: #684143; margin: 0; font-weight: 700; letter-spacing: normal;">
                                Chúc Mừng Thiệp Cưới Của {{ $coupleDisplay }}
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 36px 38px 26px 38px;">
                            <p style="font-size: 16px; color: #453738; margin: 0 0 16px 0; font-weight: 600;">
                                Thân gửi {{ $coupleDisplay }},
                            </p>
                            <p style="font-size: 14.5px; line-height: 1.85; color: #665253; margin: 0 0 24px 0;">
                                Thật tuyệt vời! Tác phẩm thiệp cưới trực tuyến mang đậm dấu ấn cá nhân của hai bạn đã được hoàn tất thành công trên hệ thống <strong>{{ $brandName }}</strong>. Từng khung hình kỷ niệm và lời ngỏ đã sẵn sàng để gửi trao niềm hạnh phúc đến những vị khách quý.
                            </p>

                            <!-- Khối Thẻ Thiệp Cưới Nổi Bật (Spotlight Card) -->
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #FDFBF9; border: 1px solid #ECE1D8; border-radius: 16px; overflow: hidden; margin-bottom: 28px;">
                                <tr>
                                    <td style="padding: 24px 26px;">
                                        <!-- Tên Cặp Đôi -->
                                        <div style="text-align: center; border-bottom: 1px dashed #E8DDD5; padding-bottom: 16px; margin-bottom: 16px;">
                                            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.16em; color: #B58E6F; font-weight: 700;">
                                                TÁC PHẨM HÔN LỄ
                                            </span>
                                            <h3 style="font-size: 21px; color: #684143; margin: 6px 0 0 0; font-weight: 700; letter-spacing: 0.02em;">
                                                {{ $coupleDisplay }}
                                            </h3>
                                        </div>

                                        <!-- Chi Tiết Hôn Lễ -->
                                        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td width="35%" valign="top" style="padding: 6px 0; font-size: 13px; color: #8C7B76; font-weight: 600;">
                                                    Mẫu Thiết Kế:
                                                </td>
                                                <td width="65%" valign="top" style="padding: 6px 0; font-size: 13.5px; color: #453738; font-weight: 600;">
                                                    {{ $templateName }}
                                                </td>
                                            </tr>
                                            @if ($formattedWeddingDate)
                                                <tr>
                                                    <td valign="top" style="padding: 6px 0; font-size: 13px; color: #8C7B76; font-weight: 600;">
                                                        Ngày Hôn Lễ:
                                                    </td>
                                                    <td valign="top" style="padding: 6px 0; font-size: 13.5px; color: #453738; font-weight: 600;">
                                                        {{ $formattedWeddingDate }}
                                                    </td>
                                                </tr>
                                            @endif
                                            @if ($venue || $address)
                                                <tr>
                                                    <td valign="top" style="padding: 6px 0; font-size: 13px; color: #8C7B76; font-weight: 600;">
                                                        Địa Điểm Tiệc:
                                                    </td>
                                                    <td valign="top" style="padding: 6px 0; font-size: 13px; color: #453738; line-height: 1.55;">
                                                        {{ $venue ? $venue : '' }}{{ $venue && $address ? ' - ' : '' }}{{ $address }}
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td valign="top" style="padding: 6px 0; font-size: 13px; color: #8C7B76; font-weight: 600;">
                                                    Liên Kết Thiệp:
                                                </td>
                                                <td valign="top" style="padding: 6px 0; font-size: 13px; color: #805052; word-break: break-all;">
                                                    <a href="{{ $publicUrl }}" target="_blank" rel="noopener noreferrer" style="color: #805052; text-decoration: underline;">
                                                        {{ $publicUrl }}
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Cụm Nút Hành Động (CTA) -->
                            <div style="text-align: center; margin: 26px 0 32px 0;">
                                <a href="{{ $publicUrl }}" target="_blank" rel="noopener noreferrer"
                                   style="display: inline-block; background-color: #805052; color: #FFFFFF; font-size: 15px; font-weight: 600; text-decoration: none; padding: 15px 38px; border-radius: 12px; letter-spacing: 0.02em; box-shadow: 0 8px 22px rgba(128, 80, 82, 0.25); margin: 6px;">
                                    ✦ Mở Xem Thiệp Cưới Trực Tuyến &rarr;
                                </a>
                                <div style="margin-top: 10px;">
                                    <a href="{{ $editorUrl }}" target="_blank" rel="noopener noreferrer"
                                       style="display: inline-block; font-size: 13px; color: #6E5C56; text-decoration: none; padding: 8px 18px; border: 1px solid #D9C8BE; border-radius: 8px; background-color: #FFFFFF; margin: 4px;">
                                        Chỉnh sửa thiệp trong Studio ↗
                                    </a>
                                </div>
                            </div>

                            <!-- Khối Phân Cách -->
                            <div style="border-top: 1px solid #F0E5DE; margin: 28px 0 24px 0;"></div>

                            <!-- Header 3 Bước Tiếp Theo -->
                            <div style="margin-bottom: 18px;">
                                <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.16em; color: #B58E6F; font-weight: 700;">
                                    HƯỚNG DẪN TIẾP THEO
                                </span>
                                <h3 style="font-size: 17px; color: #684143; margin: 6px 0 0 0; font-weight: 700;">
                                    3 Bước Hoàn Hảo Cho Ngày Vui Trọn Vẹn
                                </h3>
                            </div>

                            <!-- Bước 1 -->
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 14px;">
                                <tr>
                                    <td width="36" valign="top">
                                        <div style="width: 28px; height: 28px; line-height: 28px; border-radius: 50%; background-color: #F4E8DF; color: #805052; font-size: 13px; font-weight: 700; text-align: center;">1</div>
                                    </td>
                                    <td valign="top" style="padding-left: 10px;">
                                        <h4 style="font-size: 14.5px; color: #453738; margin: 0 0 4px 0; font-weight: 700;">Gửi duyệt thử nghiệm</h4>
                                        <p style="font-size: 13px; line-height: 1.65; color: #786663; margin: 0;">
                                            Mở xem thiệp trên cả điện thoại và máy tính để kiểm tra hình ảnh kỷ niệm, hiệu ứng mở phong bì và giai điệu bài hát cưới.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Bước 2 -->
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 14px;">
                                <tr>
                                    <td width="36" valign="top">
                                        <div style="width: 28px; height: 28px; line-height: 28px; border-radius: 50%; background-color: #F4E8DF; color: #805052; font-size: 13px; font-weight: 700; text-align: center;">2</div>
                                    </td>
                                    <td valign="top" style="padding-left: 10px;">
                                        <h4 style="font-size: 14.5px; color: #453738; margin: 0 0 4px 0; font-weight: 700;">Quản lý Khách mời &amp; Tạo mã mời riêng</h4>
                                        <p style="font-size: 13px; line-height: 1.65; color: #786663; margin: 0;">
                                            Tạo danh sách khách mời trong mục <a href="{{ $guestsUrl }}" target="_blank" rel="noopener noreferrer" style="color: #805052; font-weight: 600; text-decoration: underline;">Quản lý khách mời</a> để tự động sinh thiệp cá nhân hóa kèm mã QR check-in tiện lợi.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Bước 3 -->
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 14px;">
                                <tr>
                                    <td width="36" valign="top">
                                        <div style="width: 28px; height: 28px; line-height: 28px; border-radius: 50%; background-color: #F4E8DF; color: #805052; font-size: 13px; font-weight: 700; text-align: center;">3</div>
                                    </td>
                                    <td valign="top" style="padding-left: 10px;">
                                        <h4 style="font-size: 14.5px; color: #453738; margin: 0 0 4px 0; font-weight: 700;">Theo dõi Phản hồi (RSVP) &amp; Lời chúc</h4>
                                        <p style="font-size: 13px; line-height: 1.65; color: #786663; margin: 0;">
                                            Hệ thống tự động ghi nhận số lượng khách tham dự theo thời gian thực và lưu giữ từng dòng tâm sự chúc phúc vào Sổ ký tên điện tử.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Hộp Hỗ Trợ -->
                            <div style="background-color: #F9F4EE; border-radius: 12px; padding: 18px 22px; border-left: 3px solid #B58E6F; margin-top: 24px;">
                                <p style="font-size: 13px; line-height: 1.7; color: #6E5C56; margin: 0;">
                                    <strong style="color: #684143;">Cần hỗ trợ thêm về thiết kế?</strong> Nếu hai bạn muốn điều chỉnh riêng về bố cục ảnh, phông chữ hay định dạng nhạc nền độc quyền, hãy gửi thư tới <a href="mailto:{{ $noreplyEmail }}" style="color: #805052; text-decoration: none; font-weight: 600;">{{ $noreplyEmail }}</a> để được hỗ trợ tận tình.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 24px 30px; background-color: #FAF5F0; border-top: 1px solid #EFE4DC;">
                            <p style="font-size: 12px; color: #8A7A75; margin: 0 0 6px 0; line-height: 1.6;">
                                Thư thông báo tự động từ <strong>{{ $brandName }}</strong> &bull; Không gian thiệp cưới trực tuyến Quiet Luxury.
                            </p>
                            <p style="font-size: 11.5px; color: #9A8A85; margin: 0;">
                                &copy; {{ date('Y') }} {{ $brandName }}. Chúc hai bạn trăm năm viên mãn, ngập tràn hạnh phúc.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
