<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mừng Kỷ Niệm Ngày Cưới</title>
</head>
<body style="margin: 0; padding: 0; background-color: #FAF8F5; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #2C2824; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #FAF8F5; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #FFFFFF; border-radius: 16px; border: 1px solid #EFE8DD; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                    {{-- Header Top Decor --}}
                    <tr>
                        <td align="center" style="padding: 40px 30px 20px 30px; background: linear-gradient(180deg, #FDFBF7 0%, #FFFFFF 100%);">
                            <div style="display: inline-block; width: 44px; height: 44px; line-height: 44px; border-radius: 50%; background-color: #FAF8F5; border: 1px solid #D8CEBD; color: #C5A880; font-size: 20px;">
                                ⚜️
                            </div>
                            <h2 style="font-family: Georgia, serif; font-size: 14px; letter-spacing: 0.15em; text-transform: uppercase; color: #8C827A; margin: 16px 0 6px 0; font-weight: 500;">
                                Mây Hẹn • Wedding Keepsake
                            </h2>
                            <h1 style="font-family: Georgia, serif; font-size: 28px; line-height: 1.3; color: #1F1D1A; margin: 0; font-weight: 600;">
                                Chúc Mừng {{ $yearsMarried }} Năm Ngày Cưới
                            </h1>
                            <p style="font-size: 14px; color: #C5A880; font-weight: 600; margin: 6px 0 0 0; text-transform: uppercase; letter-spacing: 0.08em;">
                                Kỷ Niệm {{ $milestoneName }}
                            </p>
                        </td>
                    </tr>

                    {{-- Body Content --}}
                    <tr>
                        <td style="padding: 10px 40px 30px 40px;">
                            <p style="font-size: 16px; line-height: 1.7; color: #4A453E; margin: 0 0 18px 0;">
                                Thân gửi <strong>{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</strong>,
                            </p>
                            <p style="font-size: 15px; line-height: 1.7; color: #4A453E; margin: 0 0 18px 0;">
                                Thấm thoát đã trọn vẹn <strong>{{ $yearsMarried }} năm ({{ $yearsMarried * 365 }} ngày)</strong> kể từ khoảnh khắc thiêng liêng hai bạn trao nhau chiếc nhẫn cưới và lời hẹn ước trăm năm.
                            </p>
                            <p style="font-size: 15px; line-height: 1.7; color: #4A453E; margin: 0 0 24px 0;">
                                Mây Hẹn xin gửi tới hai bạn lời chúc mừng ấm áp và chân thành nhất. Chúc cho tổ ấm nhỏ của hai bạn luôn ngập tràn tiếng cười, thấu hiểu, sẻ chia và mỗi ngày trôi qua đều ngọt ngào như những ngày đầu tiên.
                            </p>

                            {{-- Card Bấm Mở Trang Kỷ Niệm --}}
                            <div style="background-color: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 12px; padding: 24px; text-align: center; margin: 24px 0;">
                                <p style="font-size: 13.5px; color: #8C827A; margin: 0 0 16px 0;">
                                    Mời hai bạn cùng mở lại cuốn nhật ký tình yêu số, xem lại những bức ảnh cưới ngày ấy và lưu giữ cảm nhận của 1 năm chung đôi:
                                </p>
                                <a href="{{ $loungeUrl }}" target="_blank" 
                                   style="display: inline-block; background-color: #1F1D1A; color: #FFFFFF; font-size: 14px; font-weight: 600; text-decoration: none; padding: 13px 28px; border-radius: 8px; letter-spacing: 0.03em;">
                                    Mở Trang Kỷ Niệm Ngày Cưới &rarr;
                                </a>
                            </div>

                            <p style="font-size: 13.5px; line-height: 1.6; color: #8C827A; margin: 24px 0 0 0; text-align: center; font-style: italic;">
                                "Tình yêu không phải là nhìn nhau, mà là cùng nhau nhìn về một hướng."
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="background-color: #F8F6F0; padding: 24px 30px; border-top: 1px solid #EFE8DD;">
                            <p style="font-size: 12px; color: #8C827A; margin: 0 0 6px 0;">
                                Email này được gửi tự động từ nền tảng thiệp cưới Mây Hẹn (mayhen.vn)
                            </p>
                            <p style="font-size: 12px; color: #8C827A; margin: 0;">
                                © {{ date('Y') }} Mây Hẹn. Giữ trọn từng khoảnh khắc thiêng liêng.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

