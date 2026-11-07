<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Thực Màn Chiếu Lễ Cưới | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,400&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #100E0D;
            color: #FAF8F5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .mayhen_pin_card {
            background: rgba(28, 25, 23, 0.9);
            border: 1px solid rgba(197, 168, 128, 0.35);
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
            max-width: 440px;
            width: 100%;
            padding: 44px 32px;
            text-align: center;
        }
        .mayhen_pin_icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(197, 168, 128, 0.15);
            border: 1px solid rgba(197, 168, 128, 0.4);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #C5A880;
            margin-bottom: 20px;
        }
        .mayhen_pin_title {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 28px;
            font-weight: 600;
            color: #FAF8F5;
            margin-bottom: 8px;
        }
        .mayhen_pin_couple {
            font-size: 14px;
            color: #C5A880;
            margin-bottom: 24px;
            letter-spacing: 0.04em;
        }
        .mayhen_pin_input {
            width: 100%;
            background: rgba(16, 14, 13, 0.8);
            border: 1px solid rgba(197, 168, 128, 0.4);
            color: #C5A880;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
            letter-spacing: 0.3em;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            outline: none;
            transition: border-color 0.2s ease;
        }
        .mayhen_pin_input:focus {
            border-color: #C5A880;
            box-shadow: 0 0 15px rgba(197, 168, 128, 0.3);
        }
        .mayhen_pin_btn {
            width: 100%;
            background: #C5A880;
            color: #1F1D1A;
            border: none;
            padding: 12px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .mayhen_pin_btn:hover {
            background: #B39265;
        }
        .mayhen_pin_error {
            color: #E57373;
            font-size: 12px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="mayhen_pin_card">
        <div class="mayhen_pin_icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>

        <h1 class="mayhen_pin_title">Bảo Vệ Màn Chiếu Lễ Cưới</h1>
        <div class="mayhen_pin_couple">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</div>

        @if($errors->has('pin'))
            <div class="mayhen_pin_error">{{ $errors->first('pin') }}</div>
        @endif

        <form action="{{ route('live_wall.verify_pin', $invitation->slug) }}" method="POST">
            @csrf
            <input type="password" 
                name="pin" 
                maxlength="10" 
                required 
                autofocus
                placeholder="••••"
                class="mayhen_pin_input">

            <button type="submit" class="mayhen_pin_btn">Mở Màn Trình Chiếu</button>
        </form>
    </div>
</body>
</html>

