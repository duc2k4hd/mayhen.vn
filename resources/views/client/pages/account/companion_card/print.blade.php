<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Thẻ Kẹp Thiệp Giấy — {{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }} | {{ config('app.name') }}</title>
    
    @include('client.components.favicon')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        /* MAYHEN.VN — PRINTABLE COMPANION SHEET (QUIET LUXURY) */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #F8F6F0;
            color: #1F1D1A;
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Top Bar Thao Tác (Ẩn khi in) */
        .mayhen_print_topbar {
            background: #FFFFFF;
            border-bottom: 1px solid #EAE3D9;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }

        .mayhen_print_topbar_info {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 13.5px;
            color: #555;
        }

        .mayhen_print_btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .mayhen_print_btn_gold {
            background: linear-gradient(135deg, #C5A880 0%, #B8935D 100%);
            color: #FFF;
        }

        .mayhen_print_btn_outline {
            background: #FFF;
            border-color: #D8CEBD;
            color: #444;
        }

        .mayhen_print_btn_outline:hover {
            border-color: #C5A880;
            color: #1F1D1A;
        }

        /* Khung Giấy A4 */
        .mayhen_a4_page {
            width: 210mm;
            min-height: 297mm;
            margin: 24px auto;
            background: #FFFFFF;
            padding: 12mm 10mm;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            position: relative;
            box-sizing: border-box;
            page-break-after: always;
            break-after: page;
        }

        /* Grid Dàn Thẻ */
        .mayhen_card_grid {
            display: grid;
            gap: 6mm;
            justify-content: center;
        }

        .grid-namecard {
            grid-template-columns: repeat(2, 90mm);
            grid-auto-rows: 54mm;
        }

        .grid-postcard {
            grid-template-columns: repeat(2, 95mm);
            grid-auto-rows: 140mm;
        }

        .grid-square {
            grid-template-columns: repeat(2, 95mm);
            grid-auto-rows: 95mm;
        }

        /* Thẻ In Thực Tế với Kích Thước Chuẩn */
        .mayhen_print_item {
            position: relative;
            border-radius: 2px;
            overflow: hidden;
            box-sizing: border-box;
            background-color: {{ $paletteMeta['bg_color'] }};
            color: {{ $paletteMeta['text_color'] }};
            border: 1px dashed rgba(197, 168, 128, 0.35);
        }

        .size-namecard {
            width: 90mm;
            height: 54mm;
            padding: 4.5mm 5.5mm;
        }

        .size-postcard {
            width: 95mm;
            height: 140mm;
            padding: 8mm 6mm;
        }

        .size-square {
            width: 95mm;
            height: 95mm;
            padding: 6mm;
        }

        /* Vạch Cắt Crop Marks */
        .crop-mark {
            position: absolute;
            background-color: #888;
            pointer-events: none;
        }
        .crop-tl-h { top: 0; left: -3mm; width: 3mm; height: 0.5px; }
        .crop-tl-v { top: -3mm; left: 0; width: 0.5px; height: 3mm; }
        .crop-tr-h { top: 0; right: -3mm; width: 3mm; height: 0.5px; }
        .crop-tr-v { top: -3mm; right: 0; width: 0.5px; height: 3mm; }
        .crop-bl-h { bottom: 0; left: -3mm; width: 3mm; height: 0.5px; }
        .crop-bl-v { bottom: -3mm; left: 0; width: 0.5px; height: 3mm; }
        .crop-br-h { bottom: 0; right: -3mm; width: 3mm; height: 0.5px; }
        .crop-br-v { bottom: -3mm; right: 0; width: 0.5px; height: 3mm; }

        /* Khung Viền & Nội Dung */
        .mayhen_item_border {
            position: absolute;
            inset: 2mm;
            border: 1px solid {{ $paletteMeta['border_color'] }};
            pointer-events: none;
        }

        .mayhen_item_border_inner {
            position: absolute;
            inset: 3mm;
            border: 0.6px dashed {{ $paletteMeta['border_color'] }};
            opacity: 0.6;
            pointer-events: none;
        }

        .mayhen_item_body {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .size-postcard .mayhen_item_body {
            flex-direction: column;
            text-align: center;
        }

        .mayhen_item_left {
            flex: 1;
            padding-right: 3mm;
        }

        .size-postcard .mayhen_item_left {
            padding-right: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .mayhen_item_kicker {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 7pt;
            font-weight: 600;
            letter-spacing: 1.5px;
            color: {{ $paletteMeta['accent_color'] }};
            text-transform: uppercase;
            margin-bottom: 1mm;
        }

        .mayhen_item_guest {
            font-size: 7pt;
            font-weight: 600;
            color: {{ $paletteMeta['accent_color'] }};
            background: {{ $paletteMeta['badge_bg'] }};
            padding: 1px 4px;
            border-radius: 2px;
            display: inline-block;
            margin-bottom: 1mm;
        }

        .mayhen_item_couple {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 11pt;
            font-weight: 700;
            line-height: 1.15;
            margin-bottom: 1mm;
        }

        .size-postcard .mayhen_item_couple {
            font-size: 15pt;
        }

        .mayhen_item_line {
            width: 8mm;
            height: 0.6px;
            background-color: {{ $paletteMeta['border_color'] }};
            margin-bottom: 1.5mm;
        }

        .mayhen_item_meta {
            font-size: 6.5pt;
            opacity: 0.9;
            margin-bottom: 1.5mm;
        }

        .mayhen_item_instructions {
            font-size: 5.5pt;
            line-height: 1.35;
            opacity: 0.8;
            margin-bottom: 1.5mm;
        }

        .mayhen_item_url {
            font-size: 5.5pt;
            font-weight: 600;
            color: {{ $paletteMeta['accent_color'] }};
        }

        .mayhen_item_right {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .mayhen_item_qr_box {
            background: #FFF;
            padding: 1.5mm;
            border-radius: 2mm;
            border: 0.6px solid {{ $paletteMeta['border_color'] }};
        }

        .mayhen_item_qr_box svg {
            display: block;
            width: 24mm;
            height: 24mm;
        }

        .size-postcard .mayhen_item_qr_box svg {
            width: 32mm;
            height: 32mm;
        }

        .mayhen_item_qr_label {
            font-size: 5.5pt;
            font-weight: 600;
            color: {{ $paletteMeta['accent_color'] }};
            margin-top: 1mm;
            text-align: center;
        }

        .mayhen_item_footer {
            position: absolute;
            bottom: 2.5mm;
            left: 3.5mm;
            font-size: 4.8pt;
            letter-spacing: 1px;
            opacity: 0.6;
        }

        .size-postcard .mayhen_item_footer {
            left: 50%;
            transform: translateX(-50%);
        }

        /* Thiết Lập Khi Bấm In (Ctrl + P) */
        @media print {
            body {
                background: none !important;
            }

            .mayhen_print_topbar {
                display: none !important;
            }

            .mayhen_a4_page {
                margin: 0 !important;
                padding: 10mm 10mm !important;
                box-shadow: none !important;
                width: 100% !important;
                min-height: auto !important;
            }

            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    {{-- Top Bar Khi Xem Trên Màn Hình --}}
    <div class="mayhen_print_topbar">
        <div class="mayhen_print_topbar_info">
            <a href="{{ route('account.companion_card.index', $invitation->id) }}" class="mayhen_print_btn mayhen_print_btn_outline">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                <span>Quay Lại Studio</span>
            </a>
            <span>{{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }} • Khổ: <strong>{{ $sizeMeta['short_name'] }}</strong></span>
            <span>Đang dàn: <strong>{{ count($guestItems) }}</strong> thẻ in</span>
        </div>

        <div style="display: flex; gap: 8px;">
            <button type="button" onclick="window.print()" class="mayhen_print_btn mayhen_print_btn_gold">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                <span>In Ngay / Lưu PDF A4</span>
            </button>
        </div>
    </div>

    {{-- Dàn Trang Khổ A4 --}}
    @php
        $itemsPerPage = $sizeMeta['items_per_a4'] ?? 10;
        $pages = array_chunk($guestItems, $itemsPerPage);
    @endphp

    @foreach($pages as $pageIdx => $itemsOnPage)
        <div class="mayhen_a4_page">
            <div class="mayhen_card_grid grid-{{ $config['size'] }}">
                @foreach($itemsOnPage as $item)
                    @php
                        $guest = $item['guest'] ?? null;
                    @endphp
                    <div class="mayhen_print_item size-{{ $config['size'] }}">
                        {{-- Crop Marks 4 Góc --}}
                        <span class="crop-mark crop-tl-h"></span>
                        <span class="crop-mark crop-tl-v"></span>
                        <span class="crop-mark crop-tr-h"></span>
                        <span class="crop-mark crop-tr-v"></span>
                        <span class="crop-mark crop-bl-h"></span>
                        <span class="crop-mark crop-bl-v"></span>
                        <span class="crop-mark crop-br-h"></span>
                        <span class="crop-mark crop-br-v"></span>

                        <div class="mayhen_item_border"></div>
                        <div class="mayhen_item_border_inner"></div>

                        <div class="mayhen_item_body">
                            <div class="mayhen_item_left">
                                @if($guest)
                                    <div class="mayhen_item_guest">Kính gửi: {{ $guest->name }}</div>
                                @endif
                                <div class="mayhen_item_kicker">{{ $config['kicker'] }}</div>
                                <h3 class="mayhen_item_couple">{{ $config['couple_title'] }}</h3>
                                <div class="mayhen_item_line"></div>
                                <div class="mayhen_item_meta">
                                    @if(!empty($config['show_date']) && $wedding->wedding_date)
                                        <span>{{ date('d.m.Y', strtotime($wedding->wedding_date)) }}</span>
                                    @endif
                                    @if(!empty($config['show_venue']) && $wedding->venue_name)
                                        <span>• {{ $wedding->venue_name }}</span>
                                    @endif
                                </div>
                                <p class="mayhen_item_instructions">{{ $config['instructions'] }}</p>
                                <div class="mayhen_item_url">mayhen.vn/{{ $invitation->slug }}</div>
                            </div>

                            <div class="mayhen_item_right">
                                <div class="mayhen_item_qr_box">
                                    {!! $item['qr_svg'] !!}
                                </div>
                                <div class="mayhen_item_qr_label">{{ $config['qr_label'] }}</div>
                            </div>
                        </div>

                        <div class="mayhen_item_footer">{{ $config['footer_brand'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</body>
</html>

