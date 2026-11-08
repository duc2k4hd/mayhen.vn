<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sổ Mừng Cưới & Đối Soát Tài Chính — {{ $wedding->groom_name }} & {{ $wedding->bride_name }} | {{ config('app.name') }}</title>
    
    <!-- Favicon -->
    @include('client.components.favicon')

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* MAYHEN.VN — WEDDING GIFT LEDGER & FINANCIAL STATEMENT PRINT STYLESHEET */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #F8F6F0;
            color: #1F1D1A;
            line-height: 1.5;
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
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(31, 29, 26, 0.05);
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
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
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
            max-width: 1000px;
            margin: 28px auto 60px;
            background: #FFFFFF;
            border: 1px solid #E8DFD3;
            border-radius: 8px;
            padding: 40px 48px;
            box-shadow: 0 10px 40px rgba(31, 29, 26, 0.06);
        }

        /* Header Tài Liệu Cưới */
        .mayhen_print_header {
            text-align: center;
            border-bottom: 2px double #D8CEBD;
            padding-bottom: 24px;
            margin-bottom: 24px;
            position: relative;
        }

        .mayhen_print_logo {
            font-family: 'Cormorant Garamond', serif;
            font-size: 16px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #9C7A4A;
            margin-bottom: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .mayhen_print_title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 32px;
            font-weight: 700;
            color: #1F1D1A;
            line-height: 1.2;
            margin-bottom: 8px;
        }

        .mayhen_print_couple {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px;
            font-weight: 600;
            color: #9C7A4A;
            margin-bottom: 12px;
        }

        .mayhen_print_meta {
            font-size: 13px;
            color: #5A534B;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        /* Thẻ Thống Kê Tài Chính */
        .mayhen_print_summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            background: #FAF8F5;
            border: 1px solid #EFE4CA;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }

        .mayhen_print_summary_item {
            text-align: center;
        }

        .mayhen_print_summary_val {
            font-family: 'Cormorant Garamond', serif;
            font-size: 24px;
            font-weight: 700;
            color: #1F1D1A;
            line-height: 1.2;
        }

        .mayhen_print_summary_lbl {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #7A726A;
            margin-top: 4px;
        }

        /* Bảng Danh Sách Mừng Cưới */
        .mayhen_print_table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            margin-bottom: 32px;
        }

        .mayhen_print_table th {
            background-color: #FAF8F5;
            color: #4A453E;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.05em;
            padding: 10px 12px;
            border-top: 1px solid #1F1D1A;
            border-bottom: 1px solid #1F1D1A;
            text-align: left;
        }

        .mayhen_print_table td {
            padding: 10px 12px;
            border-bottom: 1px solid #EAE3D9;
            color: #2C2824;
            vertical-align: middle;
        }

        .mayhen_print_table tbody tr:nth-child(even) {
            background-color: #FCFBF9;
        }

        .mayhen_print_table tbody tr {
            page-break-inside: avoid;
        }

        /* Chữ Ký Xác Nhận Cuối Trang */
        .mayhen_print_signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px dashed #D8CEBD;
            text-align: center;
            page-break-inside: avoid;
        }

        .mayhen_print_sig_title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 16px;
            font-weight: 600;
            color: #1F1D1A;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .mayhen_print_sig_desc {
            font-size: 11px;
            color: #7A726A;
            margin-bottom: 70px;
        }

        .mayhen_print_sig_name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 17px;
            font-weight: 600;
            color: #1F1D1A;
            border-top: 1px solid #C5A880;
            display: inline-block;
            padding-top: 6px;
            min-width: 200px;
        }

        /* Footer Đánh Dấu Bản Quyền */
        .mayhen_print_footer {
            margin-top: 32px;
            text-align: center;
            font-size: 11px;
            color: #9C948A;
            border-top: 1px solid #F0E8DD;
            padding-top: 12px;
        }

        /* Tối Ưu In Ấn & Xuất PDF (@media print) */
        @media print {
            body {
                background-color: #FFFFFF !important;
            }

            .mayhen_print_topbar {
                display: none !important;
            }

            .mayhen_print_container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 15mm 12mm 15mm 12mm;
            }
        }
    </style>
</head>
<body>

    <!-- Topbar Điều Khiển (Chỉ hiển thị trên màn hình máy tính / điện thoại) -->
    <div class="mayhen_print_topbar">
        <div class="mayhen_print_topbar_info">
            <a href="{{ route('account.gifts.index', $invitation->id) }}" class="mayhen_print_topbar_btn mayhen_print_topbar_btn_outline">
                &larr; Quay lại Sổ mừng cưới
            </a>
            <span>Khổ in tiêu chuẩn: <strong>A4 Portrait</strong> &bull; Hiển thị: <strong>{{ $totalGiftsCount }} khoản mừng</strong></span>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <button type="button" onclick="window.print()" class="mayhen_print_topbar_btn mayhen_print_topbar_btn_gold">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                <span>In Sổ Mừng Cưới (Ctrl + P)</span>
            </button>
        </div>
    </div>

    <!-- Khung Bản In Sổ Mừng Cưới Chuẩn A4 -->
    <div class="mayhen_print_container">
        
        <!-- Header Hôn Lễ -->
        <div class="mayhen_print_header">
            <div class="mayhen_print_logo">
                <span>🕊️</span>
                <span>Mây Hẹn &bull; Kỷ Bộ Sổ Mừng Cưới</span>
            </div>
            <h1 class="mayhen_print_title">SỔ GHI NHẬN MỪNG CƯỚI & ĐỐI SOÁT TÀI CHÍNH</h1>
            <div class="mayhen_print_couple">
                Lễ Cưới: {{ $wedding->groom_name }} &hearts; {{ $wedding->bride_name }}
            </div>
            <div class="mayhen_print_meta">
                <span><strong>Ngày thành hôn:</strong> {{ $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : 'Chưa định ngày' }}</span>
                @if(!empty($wedding->extra_data['event_party_address']))
                    <span>&bull;</span>
                    <span><strong>Sảnh tiệc:</strong> {{ $wedding->extra_data['event_party_address'] }}</span>
                @endif
                @if(!empty($filterGroup) || !empty($filterMethod) || !empty($filterAck) || !empty($filterSearch))
                    <span>&bull;</span>
                    <span><strong>Phân loại:</strong> 
                        @if(!empty($filterGroup)) Nhóm: {{ $filterGroup }} @endif
                        @if(!empty($filterMethod)) Hình thức: {{ $filterMethod === 'cash' ? 'Tiền mặt' : ($filterMethod === 'bank_transfer' ? 'Chuyển khoản' : 'Khác') }} @endif
                        @if(!empty($filterSearch)) Từ khóa: "{{ $filterSearch }}" @endif
                    </span>
                @endif
                <span>&bull;</span>
                <span><strong>Ngày in tài liệu:</strong> {{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <!-- Thống Kê Tài Chính -->
        <div class="mayhen_print_summary">
            <div class="mayhen_print_summary_item">
                <div class="mayhen_print_summary_val" style="color: #9C7A4A;">{{ number_format($totalAmount, 0, ',', '.') }}đ</div>
                <div class="mayhen_print_summary_lbl">Tổng Tiền Mừng Cưới</div>
            </div>
            <div class="mayhen_print_summary_item">
                <div class="mayhen_print_summary_val">{{ $totalGiftsCount }}</div>
                <div class="mayhen_print_summary_lbl">Số Khoản Mừng Ghi Nhận</div>
            </div>
            <div class="mayhen_print_summary_item">
                <div class="mayhen_print_summary_val" style="color: #1B5E20;">{{ number_format($bankAmount, 0, ',', '.') }}đ</div>
                <div class="mayhen_print_summary_lbl">Chuyển Khoản VietQR</div>
            </div>
            <div class="mayhen_print_summary_item">
                <div class="mayhen_print_summary_val" style="color: #0D47A1;">{{ number_format($cashAmount, 0, ',', '.') }}đ</div>
                <div class="mayhen_print_summary_lbl">Tiền Mặt / Phong Bì</div>
            </div>
        </div>

        <!-- Bảng Danh Sách Khoản Mừng Cưới -->
        <table class="mayhen_print_table">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">STT</th>
                    <th style="width: 170px;">Người Mừng Cưới</th>
                    <th style="width: 120px;">Nhóm Khách</th>
                    <th style="width: 120px; text-align: right;">Số Tiền (VNĐ)</th>
                    <th style="width: 110px; text-align: center;">Hình Thức</th>
                    <th>Lời Chúc / Ghi Chú</th>
                    <th style="width: 90px; text-align: center;">Cảm Ơn</th>
                </tr>
            </thead>
            <tbody>
                @forelse($gifts as $index => $gift)
                    <tr>
                        <td style="text-align: center; color: #7A726A;">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $gift->sender_name }}</strong>
                        </td>
                        <td>
                            <span style="font-size: 11.5px; color: #5A534B;">{{ $gift->sender_group ?? '—' }}</span>
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #1B5E20;">
                            {{ number_format($gift->amount, 0, ',', '.') }}đ
                        </td>
                        <td style="text-align: center; font-size: 11.5px;">
                            {{ $gift->payment_method_label }}
                        </td>
                        <td style="font-size: 11.5px; color: #4A453E;">
                            {{ $gift->notes ? Str::limit($gift->notes, 65) : '—' }}
                        </td>
                        <td style="text-align: center; font-size: 11px;">
                            @if($gift->is_acknowledged)
                                <span style="color: #1B5E20;">Đã gửi</span>
                            @else
                                <span style="color: #7A726A;">Chưa gửi</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 32px; color: #7A726A;">
                            Chưa có khoản mừng cưới nào được ghi nhận trong sổ.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Chữ Ký Xác Nhận Hai Bên Gia Đình & Cặp Đôi -->
        <div class="mayhen_print_signatures">
            <div>
                <div class="mayhen_print_sig_title">Đại Diện Gia Đình / Ban Tiếp Tân</div>
                <div class="mayhen_print_sig_desc">(Ký và ghi rõ họ tên người kiểm đếm)</div>
                <div class="mayhen_print_sig_name">Xác nhận đã đối soát</div>
            </div>
            <div>
                <div class="mayhen_print_sig_title">Cô Dâu & Chú Rể</div>
                <div class="mayhen_print_sig_desc">(Ký tên lưu giữ kỷ niệm hôn lễ)</div>
                <div class="mayhen_print_sig_name">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</div>
            </div>
        </div>

        <!-- Footer Ghi Chú Bản Quyền -->
        <div class="mayhen_print_footer">
            Tài liệu Sổ Mừng Cưới được tạo tự động từ Nền Tảng Thiết Kế Thiệp Cưới Trực Tuyến Cao Cấp Mây Hẹn (mayhen.vn).
        </div>

    </div>

</body>
</html>

