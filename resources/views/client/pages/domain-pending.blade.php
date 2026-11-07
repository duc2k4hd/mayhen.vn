<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tên Miền Đang Chờ Kích Hoạt | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #FAF8F5;
            color: #2C2824;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .mayhen_pending_card {
            background: #FFFFFF;
            border: 1px solid #EFE8DD;
            border-radius: 20px;
            box-shadow: 0 16px 40px rgba(44, 40, 36, 0.06);
            max-width: 520px;
            width: 100%;
            padding: 48px 36px;
            text-align: center;
        }
        .mayhen_pending_icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #FAF6F0;
            border: 1px solid #E5DCCD;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #9C7A4A;
            margin-bottom: 24px;
        }
        .mayhen_pending_title {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 30px;
            font-weight: 600;
            color: #1F1D1A;
            margin-bottom: 12px;
            line-height: 1.25;
        }
        .mayhen_pending_domain {
            display: inline-block;
            font-family: monospace;
            background: #FAF8F5;
            border: 1px dashed #D8CEBD;
            color: #9C7A4A;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 18px;
            font-weight: 600;
            word-break: break-all;
        }
        .mayhen_pending_desc {
            font-size: 14px;
            color: #6E665E;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .mayhen_pending_btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #9C7A4A;
            color: #FFFFFF;
            padding: 12px 28px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.2s ease;
        }
        .mayhen_pending_btn:hover {
            background: #85673C;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="mayhen_pending_card">
        <div class="mayhen_pending_icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="2" y1="12" x2="22" y2="12"></line>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
            </svg>
        </div>

        <h1 class="mayhen_pending_title">Tên Miền Đang Chờ Kích Hoạt</h1>
        <div class="mayhen_pending_domain">{{ $host }}</div>

        <p class="mayhen_pending_desc">
            Tên miền này đã được trỏ tới hệ thống Mây Hẹn nhưng đang trong quá trình xác thực DNS hoặc chưa được liên kết với thiệp cưới của bạn. Quá trình cập nhật DNS quốc tế thường mất từ 5 phút đến 24 giờ.
        </p>

        <a href="{{ url('/') }}" class="mayhen_pending_btn">
            <span>Trở Về Trang Chủ Mây Hẹn</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </a>
    </div>
</body>
</html>

