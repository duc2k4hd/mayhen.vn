<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Kê Suất Ăn & Bàn Tiệc Cưới — Bếp Trưởng — {{ $wedding->groom_name }} & {{ $wedding->bride_name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1F1D1A;
            background: #FFF;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            border-bottom: 2px solid #C5A880;
            padding-bottom: 12px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .title {
            font-family: "Playfair Display", Georgia, serif;
            font-size: 18px;
            font-weight: 700;
            color: #1F1D1A;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .subtitle {
            font-size: 12px;
            color: #665E55;
            margin: 0;
        }
        .kpi-row {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }
        .kpi-box {
            flex: 1;
            border: 1px solid #D9D2C7;
            border-radius: 6px;
            padding: 8px 12px;
            text-align: center;
        }
        .kpi-val {
            font-size: 18px;
            font-weight: 700;
            color: #9C7A4A;
        }
        .kpi-lbl {
            font-size: 10px;
            text-transform: uppercase;
            color: #7A726A;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        th, td {
            border: 1px solid #D9D2C7;
            padding: 6px 8px;
            font-size: 11px;
            text-align: left;
        }
        th {
            background-color: #FAF8F5;
            color: #4A433C;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 10px;
        }
        .badge-veg {
            display: inline-block;
            background: #FEF3C7;
            color: #92400E;
            padding: 1px 5px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 10px;
        }
        .footer-note {
            font-size: 10px;
            color: #8C827A;
            border-top: 1px solid #EAE3D9;
            padding-top: 10px;
            display: flex;
            justify-content: space-between;
        }
        .no-print-bar {
            background: #1F1D1A;
            color: #FFF;
            padding: 10px 16px;
            margin: -20px -20px 20px -20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #C5A880;
            color: #FFF;
            border: none;
            padding: 6px 14px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
        }
        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print-bar">
        <span>BẢNG KÊ SUẤT ĂN &amp; BÀN TIỆC — Ekip Bếp Trưởng &amp; Quản Lý Nhà Hàng</span>
        <button type="button" class="btn-print" onclick="window.print()">In Ngay (Print A4)</button>
    </div>

    <div class="header">
        <div>
            <h1 class="title">Bảng Kê Suất Ăn &amp; Điều Phối Bàn Tiệc Cưới</h1>
            <p class="subtitle">
                Đám cưới: <strong>{{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}</strong> &bull;
                Ngày cưới: <strong>{{ $wedding->wedding_date ? \Carbon\Carbon::parse($wedding->wedding_date)->format('d/m/Y') : 'Chưa đặt' }}</strong> &bull;
                Sảnh tiệc: <strong>{{ $wedding->venue_name ?: 'Sảnh chính' }}</strong>
            </p>
        </div>
        <div style="text-align: right; font-size: 11px; color: #7A726A;">
            Mây Hẹn Atelier Platform<br>
            Ngày xuất: {{ now()->format('H:i d/m/Y') }}
        </div>
    </div>

    <div class="kpi-row">
        <div class="kpi-box">
            <div class="kpi-val">{{ $metrics['confirmed_adults'] }}</div>
            <div class="kpi-lbl">Suất Người Lớn</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-val" style="color: #1D4ED8;">{{ $metrics['confirmed_children'] }}</div>
            <div class="kpi-lbl">Suất Trẻ Em / Ghế Em Bé</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-val" style="color: #B45309;">{{ $metrics['dietary_summary']['vegetarian_count'] }}</div>
            <div class="kpi-lbl">Suất Ăn Chay</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-val">{{ $metrics['tables_needed'] }}</div>
            <div class="kpi-lbl">Bàn Tiệc Tiêu Chuẩn (10 người)</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Bàn Tiệc</th>
                <th style="width: 12%;">Khu Vực</th>
                <th style="width: 10%; text-align: center;">Người Lớn</th>
                <th style="width: 10%; text-align: center;">Trẻ Em</th>
                <th style="width: 10%; text-align: center;">Tổng Khách</th>
                <th style="width: 12%; text-align: center;">Suất Chay</th>
                <th style="width: 31%;">Yêu Cầu Ăn Kiêng &amp; Dị Ứng Cụ Thể</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tables as $table)
                @php
                    $tableAdults = 0;
                    $tableChildren = 0;
                    $tableVegetarian = 0;
                    $dietaryNotes = [];

                    foreach ($table->guests as $g) {
                        $resp = $g->response;
                        if ($resp && $resp->attending_status === 'attending') {
                            $ad = max(1, (int) ($resp->adults_count ?: 1));
                            $ch = max(0, (int) ($resp->children_count ?: 0));
                            $tableAdults += $ad;
                            $tableChildren += $ch;

                            if (!empty($resp->dietary_requirements)) {
                                $dietaryNotes[] = $g->name . ': ' . $resp->dietary_requirements;
                                if (str_contains(mb_strtolower($resp->dietary_requirements), 'chay')) {
                                    $tableVegetarian++;
                                }
                            }
                        } else {
                            $tableAdults += max(1, (int) ($g->expected_attendees ?: 1));
                        }
                    }
                @endphp
                <tr>
                    <td><strong>{{ $table->name }}</strong></td>
                    <td>{{ $table->zone ?: 'Sảnh chính' }}</td>
                    <td style="text-align: center;">{{ $tableAdults }}</td>
                    <td style="text-align: center;">{{ $tableChildren }}</td>
                    <td style="text-align: center; font-weight: 600;">{{ $tableAdults + $tableChildren }}</td>
                    <td style="text-align: center;">
                        @if($tableVegetarian > 0)
                            <span class="badge-veg">{{ $tableVegetarian }} suất chay</span>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if(count($dietaryNotes) > 0)
                            <span style="color: #B45309;">{{ implode('; ', $dietaryNotes) }}</span>
                        @else
                            <span style="color: #9E9E9E; font-style: italic;">Không có</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-note">
        <span>Tài liệu phục vụ tác nghiệp Bếp Tiệc cưới Mây Hẹn (mayhen.vn).</span>
        <span>Xác nhận của Quản lý sảnh tiệc: _______________________</span>
    </div>
</body>
</html>

