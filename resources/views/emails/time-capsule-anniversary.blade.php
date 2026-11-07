<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kỷ Niệm Ngày Cưới - Mở Niêm Phong Hộp Thời Gian</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #FAF8F5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1F1D1A;
            line-height: 1.6;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 40px auto;
            background: #FFFFFF;
            border: 1px solid #EFE8DD;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(31, 29, 26, 0.06);
        }
        .email-header {
            background: #1F1D1A;
            color: #FAF8F5;
            padding: 36px 30px;
            text-align: center;
        }
        .emblem {
            display: inline-block;
            font-size: 28px;
            margin-bottom: 8px;
        }
        .header-title {
            font-family: Georgia, serif;
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin: 0 0 6px 0;
            color: #C5A880;
        }
        .header-subtitle {
            font-size: 13px;
            color: #D8CEBD;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }
        .email-body {
            padding: 36px 30px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #1F1D1A;
            margin-bottom: 14px;
        }
        .intro-text {
            font-size: 14.5px;
            color: #4A453E;
            margin-bottom: 24px;
        }
        .capsule-card {
            background: #FAF8F5;
            border: 1px solid #EAE3D9;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 28px;
        }
        .capsule-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #E5DCCD;
            font-size: 13.5px;
        }
        .capsule-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .capsule-label {
            color: #7A726A;
            font-weight: 500;
        }
        .capsule-val {
            color: #1F1D1A;
            font-weight: 600;
            text-align: right;
        }
        .cta-container {
            text-align: center;
            margin: 32px 0 20px 0;
        }
        .btn-open {
            display: inline-block;
            background: linear-gradient(135deg, #C5A880 0%, #9C7A4A 100%);
            color: #FFFFFF !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 999px;
            font-size: 14.5px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 16px rgba(156, 122, 74, 0.28);
        }
        .email-footer {
            background: #FAF8F5;
            border-top: 1px solid #EFE8DD;
            padding: 24px 30px;
            text-align: center;
            font-size: 12px;
            color: #7A726A;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            <div class="emblem">⚜️</div>
            <h1 class="header-title">HỘP THỜI GIAN KỶ NIỆM NGÀY CƯỚI</h1>
            <p class="header-subtitle">Mây Hẹn &bull; Digital Wedding Time Capsule</p>
        </div>

        <div class="email-body">
            <p class="greeting">
                Thân gửi hai bạn, {{ $wedding ? "{$wedding->groom_name} & {$wedding->bride_name}" : 'Đôi Uyên Ương' }}!
            </p>

            <p class="intro-text">
                Hôm nay là một ngày vô cùng đặc biệt! Đã đến ngày mở niêm phong <strong>Hộp Thời Gian Kỷ Niệm Hôn Nhân</strong> mà bạn hoặc những người thân thương đã gửi gắm trong ngày cưới thiêng liêng năm xưa.
            </p>

            <div class="capsule-card">
                <div class="capsule-row">
                    <span class="capsule-label">Tiêu đề tâm thư:</span>
                    <span class="capsule-val">"{{ $capsule->title }}"</span>
                </div>
                <div class="capsule-row">
                    <span class="capsule-label">Người gửi:</span>
                    <span class="capsule-val">{{ $capsule->sender_name }}</span>
                </div>
                <div class="capsule-row">
                    <span class="capsule-label">Người nhận:</span>
                    <span class="capsule-val">{{ $capsule->recipient_name }}</span>
                </div>
                <div class="capsule-row">
                    <span class="capsule-label">Cột mốc kỷ niệm:</span>
                    <span class="capsule-val">{{ $capsule->milestoneLabel() }}</span>
                </div>
                <div class="capsule-row">
                    <span class="capsule-label">Ngày hẹn mở:</span>
                    <span class="capsule-val">{{ $capsule->unlock_date ? $capsule->unlock_date->format('d/m/Y') : '' }}</span>
                </div>
            </div>

            <div class="cta-container">
                @php
                    $openUrl = $invitation 
                        ? route('time_capsule.show', ['slug' => $invitation->slug, 'capsuleId' => $capsule->id])
                        : url('/');
                @endphp
                <a href="{{ $openUrl }}" class="btn-open">
                    Mở Niêm Phong Sáp & Đọc Tâm Thư &rarr;
                </a>
            </div>

            <p style="font-size: 13px; color: #7A726A; text-align: center; margin-top: 18px;">
                Cùng nhau đọc lại những dòng tâm tư, lắng đọng cảm xúc và nhớ về khoảnh khắc hai bạn đã trao nhau lời hẹn ước trọn đời.
            </p>
        </div>

        <div class="email-footer">
            <p style="margin: 0 0 6px 0;">
                Mây Hẹn (mayhen.vn) &bull; Nền tảng thiết kế thiệp cưới & lưu giữ kỷ niệm hôn nhân cao cấp.
            </p>
            <p style="margin: 0; font-size: 11px; color: #A0988E;">
                Email này được gửi tự động vào đúng ngày kỷ niệm được thiết lập trên Hộp Thời Gian của bạn.
            </p>
        </div>
    </div>
</body>
</html>

