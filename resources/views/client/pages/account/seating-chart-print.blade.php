<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sơ Đồ Xếp Bàn Tiệc & Danh Sách Đón Khách: {{ $wedding->groom_name }} & {{ $wedding->bride_name }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #FAF8F5;
            color: #1F1D1A;
            padding: 2rem 1.5rem;
            line-height: 1.5;
        }

        .print_toolbar {
            max-width: 1000px;
            margin: 0 auto 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #FFFFFF;
            border: 1px solid #EBE5DC;
            border-radius: 12px;
            padding: 0.85rem 1.5rem;
            box-shadow: 0 4px 14px rgba(31, 29, 26, 0.04);
        }

        .print_btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.35rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }

        .print_btn--gold {
            background: linear-gradient(135deg, #C5A880 0%, #A68A56 100%);
            color: #FFFFFF;
        }

        .print_btn--outline {
            background: #FAF8F5;
            border: 1px solid #DCD5CB;
            color: #1F1D1A;
        }

        .print_container {
            max-width: 1000px;
            margin: 0 auto;
            background: #FFFFFF;
            border: 1px solid #EBE5DC;
            border-radius: 16px;
            padding: 3rem 2.5rem;
            box-shadow: 0 6px 24px rgba(31, 29, 26, 0.04);
        }

        .print_header {
            text-align: center;
            border-bottom: 2px solid #C5A880;
            padding-bottom: 1.75rem;
            margin-bottom: 2rem;
        }

        .print_badge {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            color: #8C6D3F;
            text-transform: uppercase;
            margin-bottom: 0.4rem;
        }

        .print_couple {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: #1F1D1A;
            margin-bottom: 0.35rem;
        }

        .print_meta {
            font-size: 0.95rem;
            color: #6E685F;
        }

        .print_grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .print_grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .print_table_card {
            border: 1px solid #DCD5CB;
            border-radius: 12px;
            padding: 1rem 1.15rem;
            background: #FAF8F5;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .table_head {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            border-bottom: 1px solid #E3DDD4;
            padding-bottom: 0.45rem;
            margin-bottom: 0.65rem;
        }

        .table_title {
            font-size: 1rem;
            font-weight: 700;
            color: #1F1D1A;
        }

        .table_cap {
            font-size: 0.78rem;
            font-weight: 600;
            color: #8C6D3F;
        }

        .guest_list {
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 0.85rem;
        }

        .guest_item {
            padding: 0.25rem 0;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed #EBE5DC;
            color: #38332D;
        }

        .guest_item:last-child {
            border-bottom: none;
        }

        .guest_name {
            font-weight: 500;
        }

        .guest_count {
            font-size: 0.75rem;
            color: #8C8479;
        }

        .print_footer {
            margin-top: 3rem;
            text-align: center;
            font-size: 0.82rem;
            color: #A39B8F;
            border-top: 1px solid #EBE5DC;
            padding-top: 1.5rem;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #FFFFFF !important;
                padding: 0 !important;
            }

            .print_toolbar {
                display: none !important;
            }

            .print_container {
                border: none !important;
                box-shadow: none !important;
                padding: 1cm !important;
                max-width: 100% !important;
            }

            .print_grid {
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 1.25rem !important;
            }

            .print_table_card {
                background: #FFFFFF !important;
                border: 1px solid #000000 !important;
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Toolbar Web (Ẩn khi in) -->
    <div class="print_toolbar">
        <div>
            <strong style="display: block; font-size: 1rem; color: #1F1D1A;">Sơ Đồ Xếp Bàn Tiệc & Danh Sách Đón Khách</strong>
            <span style="font-size: 0.82rem; color: #8C8479;">Khổ in tiêu chuẩn A4/A3 &bull; Tổng: {{ $tables->count() }} bàn &bull; {{ $totalAssigned }}/{{ $totalCapacity }} ghế</span>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <button type="button" class="print_btn print_btn--gold" onclick="window.print()">
                🖨️ In Ngay (Print PDF)
            </button>
            <a href="{{ route('seating.index', $invitation->id) }}" class="print_btn print_btn--outline">
                ← Quay Lại Studio
            </a>
        </div>
    </div>

    <!-- Print Document Container -->
    <div class="print_container">
        <header class="print_header">
            <span class="print_badge">SƠ ĐỒ BÀN TIỆC & BẢNG ĐIỀU PHỐI ĐÓN KHÁCH</span>
            <h1 class="print_couple">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</h1>
            <p class="print_meta">
                Ngày cưới: <strong>{{ $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : 'Chưa đặt' }}</strong> &bull;
                Sảnh tiệc: <strong>{{ $wedding->venue_name ?: 'Hôn Trường Hạnh Phúc' }}</strong>
                @if($wedding->venue_address)
                    &bull; {{ $wedding->venue_address }}
                @endif
            </p>
        </header>

        <main class="print_grid">
            @forelse($tables as $table)
                <div class="print_table_card">
                    <div class="table_head">
                        <span class="table_title">{{ $table->name }}</span>
                        <span class="table_cap">{{ $table->assigned_attendees_count }}/{{ $table->capacity }} chỗ</span>
                    </div>

                    @if($table->zone)
                        <div style="font-size: 0.72rem; color: #8C6D3F; margin-bottom: 0.4rem; font-weight: 600;">
                            Khu vực: {{ $table->zone }}
                        </div>
                    @endif

                    <ul class="guest_list">
                        @forelse($table->guests as $g)
                            <li class="guest_item">
                                <span class="guest_name">{{ $g->name }}</span>
                                <span class="guest_count">({{ $g->expected_attendees ?: 1 }} người)</span>
                            </li>
                        @empty
                            <li class="guest_item" style="color: #A39B8F; font-style: italic;">
                                (Bàn còn trống)
                            </li>
                        @endforelse
                    </ul>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #8C8479;">
                    Chưa có bàn tiệc nào được tạo trong sảnh tiệc.
                </div>
            @endforelse
        </main>

        <footer class="print_footer">
            Mây Hẹn (mayhen.vn) &bull; Nền Tảng Điều Phối Sự Kiện Cưới & Thiệp Cưới Cao Cấp
        </footer>
    </div>

</body>
</html>

