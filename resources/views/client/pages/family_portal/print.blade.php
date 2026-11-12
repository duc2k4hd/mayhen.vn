<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bản In Sổ Khách Mời — {{ $sideConfig['manager_name'] }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
            background: #FFFFFF;
            margin: 0;
            padding: 20px;
            font-size: 13pt;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 20pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 6px 0;
        }
        .meta {
            font-size: 12pt;
            color: #333333;
            margin: 4px 0;
        }
        .stats-summary {
            display: flex;
            justify-content: space-around;
            background: #F5F5F5;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #CCCCCC;
            font-size: 11pt;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
        }
        th, td {
            border: 1px solid #000000;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #EFEFEF;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .no-print {
            margin-bottom: 20px;
            text-align: right;
        }
        .btn-print {
            background: #000000;
            color: #FFFFFF;
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            font-size: 11pt;
            font-weight: bold;
            cursor: pointer;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" class="btn-print" onclick="window.print()">🖨️ Bấm Để In Ngay (Khổ A4)</button>
    </div>

    <div class="header">
        <div class="title">{{ $sideConfig['manager_name'] }}</div>
        <div class="meta">
            Lễ thành hôn: <strong>{{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}</strong>
        </div>
        @if($wedding->wedding_date)
            <div class="meta">
                Ngày cưới: <strong>{{ \Carbon\Carbon::parse($wedding->wedding_date)->format('d/m/Y') }}</strong>
                @if($wedding->venue_name)
                    • Địa điểm: <strong>{{ $wedding->venue_name }}</strong>
                @endif
            </div>
        @endif
    </div>

    <div class="stats-summary">
        <div>Tổng số khách: {{ $stats['total_guests'] }}</div>
        <div>Đã nhận lời đi: {{ $stats['attending_count'] }} ({{ $stats['attending_attendees'] }} suất)</div>
        <div>Chưa phản hồi: {{ $stats['pending_count'] }}</div>
        <div>Báo bận: {{ $stats['declined_count'] }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">STT</th>
                <th>Họ và Tên Khách Mời</th>
                <th style="width: 80px;">Xưng Hô</th>
                <th style="width: 120px;">Số Điện Thoại</th>
                <th style="width: 110px;">Nhóm Khách</th>
                <th style="width: 80px;">Bàn Tiệc</th>
                <th style="width: 60px;" class="text-center">Suất</th>
                <th style="width: 130px;">Xác Nhận Đi</th>
                <th style="width: 130px;">Ghi Chú Đón Tiếp</th>
            </tr>
        </thead>
        <tbody>
            @forelse($guests as $index => $guest)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $guest->name }}</strong></td>
                    <td class="text-center">{{ $guest->salutation }}</td>
                    <td class="text-center">{{ $guest->phone ?: '-' }}</td>
                    <td>{{ $guest->group_name }}</td>
                    <td class="text-center">{{ $guest->table_name ?: '-' }}</td>
                    <td class="text-center">{{ $guest->expected_attendees }}</td>
                    <td class="text-center">
                        @if($guest->response && $guest->response->attending_status === 'attending')
                            <strong>[✓] Có đi ({{ $guest->response->adults_count ?? 1 }})</strong>
                        @elseif($guest->response && $guest->response->attending_status === 'declined')
                            [✕] Báo bận
                        @else
                            [ ] Chưa báo
                        @endif
                    </td>
                    <td>{{ $guest->response?->wishes ?: '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 30px;">
                        Chưa có khách mời nào trong danh sách.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

