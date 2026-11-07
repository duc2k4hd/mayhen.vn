<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Mật Mã Mở Thiệp — {{ config('app.name') }}</title>
    @include('client.components.favicon')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--mh-bg);
            color: var(--mh-charcoal);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .mayhen_pwd_card {
            max-width: 440px;
            width: 100%;
            background-color: var(--mh-card-bg);
            border: 1px solid var(--mh-border);
            border-radius: 20px;
            padding: 44px 32px;
            text-align: center;
            box-shadow: 0 16px 48px rgba(31, 29, 26, 0.08);
            position: relative;
        }
        .mayhen_pwd_badge {
            display: inline-block;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 600;
            color: var(--mh-gold);
            margin-bottom: 8px;
        }
        .mayhen_pwd_icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px auto;
            border-radius: 50%;
            background-color: #FAF6F0;
            border: 1px solid #EFE4CA;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--mh-gold);
        }
        .mayhen_pwd_title {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 1.85rem;
            font-weight: 700;
            color: var(--mh-charcoal);
            margin-bottom: 8px;
            line-height: 1.25;
        }
        .mayhen_pwd_desc {
            font-size: 0.875rem;
            color: var(--mh-muted);
            line-height: 1.5;
            margin-bottom: 24px;
        }
        .mayhen_pwd_input_wrap {
            margin-bottom: 18px;
        }
        .mayhen_pwd_input {
            width: 100%;
            height: 48px;
            text-align: center;
            font-size: 18px;
            letter-spacing: 4px;
            padding: 8px 16px;
            border: 1.5px solid var(--mh-border);
            border-radius: 12px;
            background-color: #FAF8F5;
            color: var(--mh-charcoal);
            outline: none;
            transition: all 0.2s ease;
        }
        .mayhen_pwd_input:focus {
            border-color: var(--mh-gold);
            background-color: #FFFFFF;
            box-shadow: 0 0 0 3px rgba(197, 168, 128, 0.18);
        }
        .mayhen_pwd_btn {
            width: 100%;
            height: 46px;
            background-color: var(--mh-charcoal);
            color: #FFFFFF;
            border: none;
            border-radius: 12px;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .mayhen_pwd_btn:hover {
            background-color: #38332D;
            transform: translateY(-1px);
        }
        .mayhen_pwd_alert {
            background-color: #FFEBEE;
            border: 1px solid #FFCDD2;
            color: #C62828;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
            text-align: left;
        }
        .mayhen_pwd_hint {
            font-size: 12px;
            color: #A39B92;
            margin-top: 24px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="mayhen_pwd_card">
        <span class="mayhen_pwd_badge">THIỆP CƯỚI RIÊNG TƯ</span>

        <div class="mayhen_pwd_icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>

        @php
            $coupleName = 'Cô Dâu & Chú Rể';
            if ($wedding && ($wedding->groom_name || $wedding->bride_name)) {
                $coupleName = trim(($wedding->groom_name ?? '') . ' & ' . ($wedding->bride_name ?? ''));
            }
        @endphp

        <h1 class="mayhen_pwd_title">{{ $coupleName }}</h1>
        <p class="mayhen_pwd_desc">
            Thiệp cưới này được cài đặt chế độ bảo mật riêng tư. Vui lòng nhập mật mã để mở xem thiệp.
        </p>

        @if(session('password_error'))
            <div class="mayhen_pwd_alert">
                {{ session('password_error') }}
            </div>
        @endif

        <form action="{{ route('invitation.unlock', $invitation->slug) }}" method="POST">
            @csrf
            <div class="mayhen_pwd_input_wrap">
                <input type="password" name="password" required autofocus placeholder="Nhập mã bảo mật..." class="mayhen_pwd_input" maxlength="20">
            </div>

            <button type="submit" class="mayhen_pwd_btn">
                <span>Mở Thiệp Cưới</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>
        </form>

        <p class="mayhen_pwd_hint">
            💡 Gợi ý: Hãy liên hệ trực tiếp với Cô Dâu hoặc Chú Rể nếu bạn chưa nhận được mật mã mở thiệp.
        </p>
    </div>
</body>
</html>

