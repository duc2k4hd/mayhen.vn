<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Thực Mã PIN — {{ $sideConfig['manager_name'] }} | Mây Hẹn</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/client/css/common/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/common/base.css') }}">
    <style>
        body {
            background: #FAF8F5;
            color: #1F1D1A;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            box-sizing: border-box;
        }
        .mayhen_pin_card {
            background: #FFFFFF;
            border: 1px solid #EAE3D9;
            border-radius: 20px;
            padding: 36px 28px;
            width: 100%;
            max-width: 420px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0,0,0,0.06);
        }
        .mayhen_pin_badge {
            display: inline-block;
            background: #FAF8F5;
            border: 1px solid #EAE3D9;
            color: #9C7A4A;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 14px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }
        .mayhen_pin_title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
            font-weight: 700;
            color: #1F1D1A;
            margin: 0 0 6px 0;
            line-height: 1.2;
        }
        .mayhen_pin_couple {
            font-size: 14px;
            color: #8C827A;
            margin: 0 0 24px 0;
        }
        .mayhen_pin_input {
            width: 100%;
            box-sizing: border-box;
            padding: 14px 16px;
            border: 2px solid #D8CEBD;
            border-radius: 12px;
            font-size: 24px;
            font-family: monospace;
            text-align: center;
            letter-spacing: 6px;
            font-weight: 700;
            background: #FAF8F5;
            color: #1F1D1A;
            margin-bottom: 16px;
            outline: none;
            transition: border-color 0.2s;
        }
        .mayhen_pin_input:focus {
            border-color: #9C7A4A;
            background: #FFFFFF;
        }
        .mayhen_pin_btn {
            width: 100%;
            background: #1F1D1A;
            color: #FFFFFF;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .mayhen_pin_btn:hover {
            background: #9C7A4A;
        }
        .mayhen_keypad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 20px;
        }
        .mayhen_key_btn {
            background: #FAF8F5;
            border: 1px solid #EAE3D9;
            border-radius: 10px;
            padding: 14px 0;
            font-size: 20px;
            font-weight: 600;
            color: #1F1D1A;
            cursor: pointer;
            transition: background 0.1s, transform 0.1s;
        }
        .mayhen_key_btn:active {
            background: #EAE3D9;
            transform: scale(0.96);
        }
    </style>
</head>
<body>
    <div class="mayhen_pin_card">
        <span class="mayhen_pin_badge">
            {{ $side === 'nha-trai' ? '👑 Cổng Họ Nhà Trai' : '🌸 Cổng Họ Nhà Gái' }}
        </span>

        <h1 class="mayhen_pin_title">{{ $sideConfig['manager_name'] }}</h1>
        <p class="mayhen_pin_couple">
            Đám cưới: <strong>{{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}</strong>
        </p>

        @if(session('error'))
            <div style="background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; padding: 12px; border-radius: 8px; margin-bottom: 18px; font-size: 13.5px;">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('family_portal.verify_pin', ['slug' => $invitation->slug, 'side' => $side]) }}" method="POST" id="pin_form">
            @csrf
            <div style="margin-bottom: 12px; font-size: 13.5px; color: #666;">
                Vui lòng nhập mã PIN bảo mật do cô dâu chú rể cung cấp:
            </div>

            <input type="password" name="pin" id="pin_display" class="mayhen_pin_input" maxlength="10" placeholder="••••" required autofocus autocomplete="off">

            <button type="submit" class="mayhen_pin_btn">
                Mở Sổ Khách Mời &rarr;
            </button>
        </form>

        {{-- Bàn phím số bấm tay cho người lớn tuổi --}}
        <div class="mayhen_keypad">
            <button type="button" class="mayhen_key_btn" onclick="pressDigit('1')">1</button>
            <button type="button" class="mayhen_key_btn" onclick="pressDigit('2')">2</button>
            <button type="button" class="mayhen_key_btn" onclick="pressDigit('3')">3</button>
            <button type="button" class="mayhen_key_btn" onclick="pressDigit('4')">4</button>
            <button type="button" class="mayhen_key_btn" onclick="pressDigit('5')">5</button>
            <button type="button" class="mayhen_key_btn" onclick="pressDigit('6')">6</button>
            <button type="button" class="mayhen_key_btn" onclick="pressDigit('7')">7</button>
            <button type="button" class="mayhen_key_btn" onclick="pressDigit('8')">8</button>
            <button type="button" class="mayhen_key_btn" onclick="pressDigit('9')">9</button>
            <button type="button" class="mayhen_key_btn" style="color: #9B1C1C; font-size: 14px;" onclick="clearPin()">Xóa</button>
            <button type="button" class="mayhen_key_btn" onclick="pressDigit('0')">0</button>
            <button type="button" class="mayhen_key_btn" style="color: #555; font-size: 18px;" onclick="backspacePin()">⌫</button>
        </div>

        <div style="margin-top: 24px; font-size: 12px; color: #8C827A;">
            Mây Hẹn • Bảo Mật Riêng Tư Tuyệt Đối Gia Đình
        </div>
    </div>

    <script>
        const pinInput = document.getElementById('pin_display');

        function pressDigit(d) {
            if (pinInput.value.length < 10) {
                pinInput.value += d;
            }
        }

        function clearPin() {
            pinInput.value = '';
        }

        function backspacePin() {
            pinInput.value = pinInput.value.slice(0, -1);
        }
    </script>
</body>
</html>

