<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Thiệp Cưới Chưa Xuất Bản — {{ config('app.name') }}</title>
    @include('client.components.favicon')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --mh-bg: #FAF8F5;
            --mh-card-bg: #FFFFFF;
            --mh-border: #E8E2D8;
            --mh-charcoal: #1F1D1A;
            --mh-muted: #7A756E;
            --mh-gold: #C5A880;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: var(--mh-bg);
            color: var(--mh-charcoal);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .mayhen_status_container {
            max-width: 520px;
            width: 100%;
            background-color: var(--mh-card-bg);
            border: 1px solid var(--mh-border);
            border-radius: 16px;
            padding: 40px 32px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(31, 29, 26, 0.05);
        }
        .mayhen_status_icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 20px auto;
            border-radius: 50%;
            background-color: #FAF6F0;
            border: 1px solid #EFE4CA;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--mh-gold);
        }
        .mayhen_status_title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--mh-charcoal);
            margin-bottom: 12px;
        }
        .mayhen_status_desc {
            font-size: 0.875rem;
            color: var(--mh-muted);
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .mayhen_status_btn {
            display: inline-block;
            background-color: var(--mh-charcoal);
            color: #FFFFFF;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .mayhen_status_btn:hover {
            background-color: var(--mh-gold);
            color: #FFFFFF;
        }
    </style>
</head>
<body>
    <div class="mayhen_status_container">
        <div class="mayhen_status_icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>
        <h1 class="mayhen_status_title">Thiệp Cưới Chưa Xuất Bản</h1>
        <p class="mayhen_status_desc">
            Thiệp cưới này hiện đang trong quá trình chuẩn bị hoặc ở chế độ riêng tư của cặp đôi và chưa được phát hành công khai.
        </p>
        <a href="{{ route('home') }}" class="mayhen_status_btn">Về Trang Chủ Mây Hẹn</a>
    </div>
</body>
</html>

