<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sổ Lưu Bút Kỷ Niệm — {{ $wedding->groom_name }} & {{ $wedding->bride_name }} | {{ config('app.name') }}</title>
    
    <!-- Favicon -->
    @include('client.components.favicon')

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        /* MAYHEN.VN — KEEPSAKE GUESTBOOK PRINT STYLESHEET (QUIET LUXURY) */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #F8F6F0;
            color: #1F1D1A;
            line-height: 1.6;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Top Bar Thao Tác (Ẩn khi in) */
        .mayhen_print_topbar {
            background: #FFFFFF;
            border-bottom: 1px solid #EAE3D9;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(31, 29, 26, 0.05);
        }

        .mayhen_print_topbar_info {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            color: #5A534B;
        }

        .mayhen_print_topbar_btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .mayhen_print_topbar_btn_gold {
            background: linear-gradient(135deg, #C5A880 0%, #B8935D 100%);
            color: #FFFFFF;
        }

        .mayhen_print_topbar_btn_gold:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }

        .mayhen_print_topbar_btn_outline {
            background: #FFFFFF;
            border-color: #D8CEBD;
            color: #4A453E;
        }

        .mayhen_print_topbar_btn_outline:hover {
            border-color: #1F1D1A;
            color: #1F1D1A;
        }

        /* Khung Khổ Sách In A4 */
        .mayhen_print_container {
            max-width: 900px;
            margin: 32px auto 60px;
            background: #FFFDF9;
            border: 1px solid #E8DFD3;
            border-radius: 8px;
            padding: 48px 56px;
            box-shadow: 0 10px 40px rgba(31, 29, 26, 0.06);
        }

        /* Bìa & Header Trang Trọng */
        .mayhen_print_header {
            text-align: center;
            padding-bottom: 32px;
            margin-bottom: 36px;
            border-bottom: 2px solid #EFE8DD;
            position: relative;
        }

        .mayhen_print_header::after {
            content: '❖';
            position: absolute;
            bottom: -11px;
            left: 50%;
            transform: translateX(-50%);
            background: #FFFDF9;
            padding: 0 14px;
            color: #C5A880;
            font-size: 16px;
        }

        .mayhen_print_badge {
            display: inline-block;
            font-size: 11px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #9C7A4A;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .mayhen_print_title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 40px;
            font-weight: 600;
            color: #1F1D1A;
            line-height: 1.15;
            margin-bottom: 10px;
        }

        .mayhen_print_subtitle {
            font-size: 14px;
            color: #7A726A;
            margin-bottom: 8px;
        }

        .mayhen_print_meta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            font-size: 13px;
            color: #5A534B;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .mayhen_print_meta_item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Lưới Thẻ Lời Chúc (Wish Cards) */
        .mayhen_print_wishes_grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .mayhen_print_wish_card {
            background: #FFFFFF;
            border: 1px solid #EAE3D9;
            border-left: 3px solid #C5A880;
            border-radius: 12px;
            padding: 20px 24px;
            position: relative;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .mayhen_print_wish_header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #F0EBE3;
        }

        .mayhen_print_wish_sender {
            font-family: 'Cormorant Garamond', serif;
            font-size: 20px;
            font-weight: 700;
            color: #1F1D1A;
        }

        .mayhen_print_wish_time {
            font-size: 11px;
            color: #8C827A;
            white-space: nowrap;
        }

        .mayhen_print_wish_message {
            font-size: 14px;
            color: #3B3733;
            line-height: 1.7;
            font-style: italic;
            position: relative;
        }

        .mayhen_print_wish_reply {
            margin-top: 14px;
            padding: 12px 16px;
            background: #FAF8F5;
            border-radius: 8px;
            border-left: 2px solid #C5A880;
            font-size: 13px;
            color: #5A534B;
        }

        .mayhen_print_wish_reply_title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #9C7A4A;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Khung Chữ Ký Uyên Ương Cuối Sách */
        .mayhen_print_footer {
            margin-top: 48px;
            padding-top: 32px;
            border-top: 1px solid #EAE3D9;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .mayhen_print_footer_note {
            text-align: center;
            font-style: italic;
            font-size: 13px;
            color: #7A726A;
            max-width: 580px;
            margin: 0 auto 36px;
            line-height: 1.7;
        }

        .mayhen_print_signatures {
            display: flex;
            justify-content: space-around;
            text-align: center;
            padding: 0 40px;
        }

        .mayhen_print_sign_box {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .mayhen_print_sign_label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #8C827A;
        }

        .mayhen_print_sign_name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px;
            font-weight: 600;
            color: #1F1D1A;
            margin-top: 40px;
            border-top: 1px solid #1F1D1A;
            padding-top: 6px;
            min-width: 160px;
        }

        /* ==========================================================================
           QUY TẮC IN ẤN (@media print)
           ========================================================================== */
        @media print {
            body {
                background: #FFFFFF !important;
                color: #000000 !important;
            }

            .mayhen_print_topbar {
                display: none !important;
            }

            .mayhen_print_container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 20px 24px !important;
                border: none !important;
                box-shadow: none !important;
                background: #FFFFFF !important;
            }

            .mayhen_print_wish_card {
                border-color: #D8CEBD !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            @page {
                size: A4;
                margin: 15mm 15mm 15mm 15mm;
            }
        }
    </style>
</head>
<body>
    <!-- Thanh Thao Tác Khi Xem Trên Màn Hình -->
    <div class="mayhen_print_topbar">
        <div class="mayhen_print_topbar_info">
            <a href="{{ route('account.wishes.index', $invitation->id) }}" class="mayhen_print_topbar_btn mayhen_print_topbar_btn_outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Quay Lại Sổ Lưu Bút</span>
            </a>
            <span>Đang xem: <strong>{{ count($wishes) }}</strong> lời chúc kỷ niệm</span>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #5A534B;">
                <span>Bộ lọc:</span>
                <select onchange="window.location.href = this.value" style="padding: 6px 12px; border-radius: 8px; border: 1px solid #D8CEBD; font-family: inherit; font-size: 13px; background: #FFFFFF; cursor: pointer;">
                    <option value="{{ route('account.wishes.print', ['id' => $invitation->id, 'status' => 'approved']) }}" {{ $statusFilter === 'approved' ? 'selected' : '' }}>
                        Lời chúc đã duyệt ({{ $approvedCount }})
                    </option>
                    <option value="{{ route('account.wishes.print', ['id' => $invitation->id, 'status' => 'all']) }}" {{ $statusFilter === 'all' ? 'selected' : '' }}>
                        Tất cả lời chúc ({{ $totalCount }})
                    </option>
                </select>
            </div>

            <button type="button" onclick="window.print()" class="mayhen_print_topbar_btn mayhen_print_topbar_btn_gold">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                <span>In Sổ Kỷ Yếu / Lưu PDF</span>
            </button>
        </div>
    </div>

    <!-- Khung Khổ Giấy In Kỷ Yếu -->
    <main class="mayhen_print_container">
        <!-- Header Trang Bìa Lưu Bút -->
        <header class="mayhen_print_header">
            <span class="mayhen_print_badge">Sổ Lưu Bút Kỷ Niệm Ngày Cưới</span>
            <h1 class="mayhen_print_title">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</h1>
            <p class="mayhen_print_subtitle">Những lời chúc phúc chân thành và trân quý từ người thân, bạn bè hai họ</p>

            <div class="mayhen_print_meta">
                @if($wedding->wedding_date)
                    <div class="mayhen_print_meta_item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9C7A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span>Hôn lễ: <strong>{{ \Carbon\Carbon::parse($wedding->wedding_date)->format('d/m/Y') }}</strong></span>
                    </div>
                @endif

                @if(!empty($wedding->extra_data['event_party_address']))
                    <div class="mayhen_print_meta_item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9C7A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <span>{{ $wedding->extra_data['event_party_address'] }}</span>
                    </div>
                @endif

                <div class="mayhen_print_meta_item">
                    <span>Tổng số: <strong>{{ count($wishes) }}</strong> lời chúc</span>
                </div>
            </div>
        </header>

        <!-- Danh Sách Lời Chúc Kỷ Yếu -->
        <section class="mayhen_print_wishes_grid">
            @forelse($wishes as $wish)
                <article class="mayhen_print_wish_card">
                    <div class="mayhen_print_wish_header">
                        <div class="mayhen_print_wish_sender">
                            {{ $wish->sender_name }}
                            @if($wish->is_pinned)
                                <span style="font-size: 11px; color: #B78103; font-weight: 500; margin-left: 4px;">[📌 Ghim]</span>
                            @endif
                        </div>
                        <div class="mayhen_print_wish_time">{{ $wish->created_at ? $wish->created_at->format('d/m/Y H:i') : '' }}</div>
                    </div>
                    <div class="mayhen_print_wish_message">
                        “{{ $wish->message }}”
                    </div>
                    @if($wish->photo_url)
                        <div style="margin-top: 10px; border-radius: 6px; overflow: hidden; max-height: 140px; display: inline-block; border: 1px solid #EAE3D9;">
                            <img src="{{ $wish->photo_url }}" alt="Ảnh kỷ niệm {{ $wish->sender_name }}" style="max-height: 140px; max-width: 200px; object-fit: cover; display: block;">
                        </div>
                    @endif
                    @if($wish->signature_url)
                        <div style="margin-top: 8px; text-align: right;">
                            <img src="{{ $wish->signature_url }}" alt="Chữ ký {{ $wish->sender_name }}" style="height: 28px; max-width: 120px; object-fit: contain;">
                        </div>
                    @endif
                    @if($wish->reply)
                        <div class="mayhen_print_wish_reply">
                            <div class="mayhen_print_wish_reply_title">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>Lời cảm ơn từ Cô Dâu & Chú Rể</span>
                            </div>
                            <div>{{ $wish->reply }}</div>
                        </div>
                    @endif
                </article>
            @empty
                <div style="text-align: center; padding: 60px 20px; color: #8C827A;">
                    Chưa có lời chúc nào trong danh sách.
                </div>
            @endforelse
        </section>

        <!-- Chân Trang Kỷ Yếu: Lời Tri Ân & Chữ Ký Cặp Đôi -->
        <footer class="mayhen_print_footer">
            <p class="mayhen_print_footer_note">
                “Tình yêu không chỉ là nhìn về nhau, mà là cùng nhìn về một hướng. Xin chân thành cảm ơn gia đình, thầy cô, bạn bè đã đến chung vui và gửi gắm những lời chúc phúc ấm áp nhất cho khởi đầu hạnh phúc của chúng mình.”
            </p>

            <div class="mayhen_print_signatures">
                <div class="mayhen_print_sign_box">
                    <span class="mayhen_print_sign_label">Chú Rể</span>
                    <span class="mayhen_print_sign_name">{{ $wedding->groom_name }}</span>
                </div>
                <div class="mayhen_print_sign_box">
                    <span class="mayhen_print_sign_label">Cô Dâu</span>
                    <span class="mayhen_print_sign_name">{{ $wedding->bride_name }}</span>
                </div>
            </div>
        </footer>
    </main>
</body>
</html>

