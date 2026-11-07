<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sách Kỷ Yếu Lưu Bút: {{ $wedding->groom_name }} & {{ $wedding->bride_name }} | {{ config('app.name') }}</title>
    @include('client.components.favicon')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/keepsake-book.css') }}">
</head>
<body class="mayhen_book_body">

    {{-- Screen Only Action Bar --}}
    <div class="mayhen_book_screen_bar">
        <div class="screen_bar_container">
            <div class="screen_bar_info">
                <strong>📖 Cuốn Sách Kỷ Yếu Lưu Bút</strong> &bull; {{ $totalCount }} lời chúc phúc
            </div>
            <div class="screen_bar_actions">
                <button type="button" class="btn_print" onclick="window.print()">
                    🖨️ In Hoặc Lưu File PDF (Ctrl+P)
                </button>
                <a href="{{ route('memory.vault', $invitation->slug) }}" class="btn_close">
                    Đóng ✕
                </a>
            </div>
        </div>
    </div>

    {{-- The Keepsake Book Pages Container --}}
    <div class="mayhen_book_container">

        {{-- PAGE 1: COVER PAGE (TRANG BÌA SÁCH) --}}
        <div class="mayhen_book_page mayhen_book_page--cover">
            <div class="cover_border">
                <div class="cover_emblem">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#C5A880" stroke-width="1.2">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"></path>
                    </svg>
                </div>

                <span class="cover_super_title">KỶ YẾU LƯU BÚT NGÀY CƯỚI</span>
                <h1 class="cover_couple">{{ $wedding->groom_name }}<br><span class="cover_amp">&</span><br>{{ $wedding->bride_name }}</h1>

                <div class="cover_divider">
                    <span class="divider_ornament">✦</span>
                </div>

                <p class="cover_date">
                    {{ $wedding->wedding_date ? $wedding->wedding_date->translatedFormat('l, d \t\h\á\n\g m, Y') : 'Hôn Lễ Thiêng Liêng' }}
                </p>

                <div class="cover_footer">
                    <span>MÂY HẸN &bull; DIGITAL WEDDING KEEPSAKE BOOK</span>
                </div>
            </div>
        </div>

        {{-- PAGE 2: PREFACE / THANK YOU LETTER (TRANG MỞ ĐẦU) --}}
        <div class="mayhen_book_page mayhen_book_page--preface">
            <div class="page_header_mark">
                <span>LỜI MỞ ĐẦU & TRI ÂN</span>
            </div>

            <div class="preface_body">
                <h2 class="preface_title">Gửi Những Người Thân Thương</h2>
                <div class="preface_letter">
                    <p>{{ $thankYouLetter }}</p>
                </div>

                <div class="preface_signatures">
                    <div class="sig_line"></div>
                    <p class="sig_couple">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</p>
                </div>
            </div>

            <div class="page_footer_mark">
                <span>— Trang 2 —</span>
            </div>
        </div>

        {{-- WISHES PAGES: Chunks of 3 wishes per page --}}
        @php
            $wishesChunks = $wishes->chunk(3);
            $pageNumber = 3;
        @endphp

        @forelse($wishesChunks as $chunk)
            <div class="mayhen_book_page mayhen_book_page--wishes">
                <div class="page_header_mark">
                    <span>SỔ LƯU BÚT TÌNH YÊU &bull; {{ $wedding->groom_name }} & {{ $wedding->bride_name }}</span>
                </div>

                <div class="wishes_body">
                    @foreach($chunk as $wish)
                        <div class="book_wish_entry">
                            <div class="wish_entry_header">
                                <strong class="wish_author">{{ $wish->name }}</strong>
                                <span class="wish_date">{{ $wish->created_at ? $wish->created_at->format('d/m/Y') : '' }}</span>
                            </div>
                            <blockquote class="wish_quote">
                                “{{ $wish->message }}”
                            </blockquote>
                            @if($wish->reply)
                                <div class="wish_reply_entry">
                                    <span class="reply_lead">Phản hồi từ Cô dâu & Chú rể:</span>
                                    <p class="reply_text">{{ $wish->reply }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="page_footer_mark">
                    <span>— Trang {{ $pageNumber++ }} —</span>
                </div>
            </div>
        @empty
            <div class="mayhen_book_page">
                <div class="page_header_mark"><span>SỔ LƯU BÚT</span></div>
                <div class="wishes_body" style="text-align: center; padding: 60px 20px;">
                    <p>Chưa có lời chúc nào trong cuốn sổ lưu bút này.</p>
                </div>
                <div class="page_footer_mark"><span>— Trang 3 —</span></div>
            </div>
        @endforelse

        {{-- FINAL PAGE: CLOSING BACK COVER (TRANG BÌA SAU) --}}
        <div class="mayhen_book_page mayhen_book_page--back_cover">
            <div class="back_cover_inner">
                <div class="back_ornament">⚜️</div>
                <h3 class="back_couple">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</h3>
                <p class="back_quote">"Tình yêu không làm cho thế giới quay tròn. Tình yêu là điều khiến chuyến đi đó trở nên đáng giá."</p>
                <div class="back_brand">MAYHEN.VN &bull; QUIET LUXURY WEDDING INVITATIONS</div>
            </div>
        </div>

    </div>

</body>
</html>

