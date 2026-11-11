<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sổ Lưu Niệm Ngày Cưới: {{ $wedding->groom_name }} & {{ $wedding->bride_name }} | Mây Hẹn Atelier</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/keepsake.css') }}?v={{ time() }}">
    <style>
        body {
            background-color: #F8F6F0;
            margin: 0;
            padding: 30px 16px;
            font-family: 'Inter', sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .mayhen_action_toolbar {
            max-width: 820px;
            margin: 0 auto 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mayhen_btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
        }

        .mayhen_btn_primary {
            background: #1F1D1A;
            color: #FFFFFF;
            border: 1px solid #1F1D1A;
        }

        .mayhen_btn_outline {
            background: transparent;
            color: #1F1D1A;
            border: 1px solid #D6D0C7;
        }
    </style>
</head>
<body>
    {{-- Thanh công cụ trước khi in --}}
    <div class="mayhen_action_toolbar mayhen_book_no_print">
        <a href="{{ route('account.keepsake.index', $invitation->id) }}" class="mayhen_btn mayhen_btn_outline">
            &larr; Quay lại Quản Lý Kỷ Niệm
        </a>
        <button type="button" onclick="window.print()" class="mayhen_btn mayhen_btn_primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            <span>In Sổ Lưu Niệm / Lưu PDF</span>
        </button>
    </div>

    {{-- Trang Sổ Lưu Niệm Chuẩn Khổ A4 --}}
    <div class="mayhen_book_wrapper">
        {{-- Trang Bìa --}}
        <div class="mayhen_book_cover">
            <div class="mayhen_book_crest">✦</div>
            <h1 class="mayhen_book_title">SỔ LƯU NIỆM NGÀY CƯỚI</h1>
            <div class="mayhen_book_couple">
                {{ $wedding->groom_name }} & {{ $wedding->bride_name }}
            </div>
            <div class="mayhen_book_date">
                {{ $wedding->wedding_date ? \Carbon\Carbon::parse($wedding->wedding_date)->format('d . m . Y') : '' }}
                @if($wedding->venue_name)
                    • {{ $wedding->venue_name }}
                @endif
            </div>
        </div>

        {{-- Phần 1: Lời Tri Ân Từ Đôi Uyên Ương --}}
        <div>
            <h2 class="mayhen_book_section_title">
                {{ $keepsake->thank_you_title ?: 'Lời Cảm Ơn Chân Thành Từ Chúng Mình' }}
            </h2>
            <div class="mayhen_book_letter">
                {{ $keepsake->thank_you_message ?: \App\Services\WeddingKeepsakeService::DEFAULT_THANK_YOU_MESSAGE }}
            </div>
        </div>

        {{-- Phần 2: Thống Kê & Dấu Ấn Hôn Lễ --}}
        <div style="background: #FAF8F5; border-top: 1px solid #EFE8DD; border-bottom: 1px solid #EFE8DD; padding: 20px; margin: 40px 0; text-align: center; display: flex; justify-content: space-around; gap: 16px;">
            <div>
                <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; color: #7A726A;">Số Lời Chúc Phúc</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #9C7A4A; font-family: 'Cormorant Garamond', serif;">{{ $wishes_count }}</div>
            </div>
            <div>
                <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; color: #7A726A;">Quan Khách Chung Vui</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #9C7A4A; font-family: 'Cormorant Garamond', serif;">{{ $attended_guests_count }}</div>
            </div>
            <div>
                <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; color: #7A726A;">Thời Điểm Xuất Sổ</div>
                <div style="font-size: 1.1rem; font-weight: 600; color: #1F1D1A; margin-top: 6px;">{{ $generated_at->format('d/m/Y') }}</div>
            </div>
        </div>

        {{-- Phần 3: Toàn Bộ Lời Chúc & Lưu Bút Phúc Lành --}}
        <div>
            <h2 class="mayhen_book_section_title">
                Lưu Bút & Lời Chúc Phúc Từ Quan Khách
            </h2>

            @if($wishes->isEmpty())
                <div style="text-align: center; padding: 40px; color: #7A726A; font-style: italic; font-size: 1.1rem;">
                    Chưa có lời chúc nào được gửi qua thiệp cưới trực tuyến.
                </div>
            @else
                <div class="mayhen_book_wishes_grid">
                    @foreach($wishes as $wish)
                        <div class="mayhen_book_wish_card">
                            <div class="mayhen_book_wish_sender">
                                {{ $wish->name ?: 'Một người bạn thân quý' }}
                            </div>
                            <div class="mayhen_book_wish_text">
                                "{{ $wish->message }}"
                            </div>
                            <div style="font-size: 0.8rem; color: #9C948A; margin-top: 10px; font-family: 'Inter', sans-serif;">
                                {{ $wish->created_at ? $wish->created_at->format('d/m/Y H:i') : '' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Lời kết & Con dấu Mây Hẹn --}}
        <div class="mayhen_book_footer">
            <div style="font-size: 1.3rem; color: #C5A880; margin-bottom: 8px;">✦</div>
            <div style="letter-spacing: 0.15em; text-transform: uppercase; font-size: 0.8rem; font-weight: 600;">
                MÂY HẸN ATELIER • KỶ NIỆM HÔN LỄ TRỌN VẸN
            </div>
            <div style="font-size: 0.75rem; color: #A8A196; margin-top: 4px; font-family: 'Inter', sans-serif;">
                Tác phẩm số độc bản lưu giữ những khoảnh khắc quý báu ngày chung đôi.
            </div>
        </div>
    </div>
</body>
</html>
