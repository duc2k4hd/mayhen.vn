<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa Đơn #{{ $invoice->invoice_number ?? $invoice->id }} - Mây Hẹn Atelier</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #F4F1EA;
            color: #2B2927;
            padding: 30px;
            font-size: 14px;
            line-height: 1.5;
        }
        .invoice_container {
            max-width: 800px;
            margin: 0 auto;
            background: #FFFFFF;
            padding: 48px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            position: relative;
        }
        .invoice_header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #EBE7DF;
            padding-bottom: 24px;
            margin-bottom: 24px;
        }
        .brand_title {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 700;
            color: #8C6B37;
            letter-spacing: 0.02em;
        }
        .brand_tagline {
            font-size: 12px;
            color: #7A756D;
            margin-top: 2px;
        }
        .company_info {
            font-size: 12px;
            color: #5A554E;
            line-height: 1.6;
            margin-top: 8px;
        }
        .invoice_meta {
            text-align: right;
        }
        .invoice_title {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 700;
            color: #2B2927;
            text-transform: uppercase;
        }
        .invoice_number {
            font-family: monospace;
            font-size: 15px;
            font-weight: 700;
            color: #8C6B37;
            margin-top: 4px;
        }
        .party_grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }
        .party_box {
            background: #FAF9F6;
            border: 1px solid #EBE7DF;
            border-radius: 6px;
            padding: 16px;
        }
        .party_title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #8C6B37;
            margin-bottom: 8px;
            border-bottom: 1px solid #EBE7DF;
            padding-bottom: 4px;
        }
        .party_row {
            margin-bottom: 4px;
            font-size: 13px;
        }
        table.invoice_table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        table.invoice_table th {
            background: #FAF9F6;
            border: 1px solid #EBE7DF;
            padding: 10px 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #5A554E;
        }
        table.invoice_table td {
            border: 1px solid #EBE7DF;
            padding: 12px;
            font-size: 13px;
        }
        .summary_grid {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        .summary_box {
            width: 320px;
        }
        .summary_row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
        }
        .summary_total {
            border-top: 2px solid #8C6B37;
            margin-top: 8px;
            padding-top: 10px;
            font-size: 16px;
            font-weight: 700;
            color: #8C6B37;
        }
        .signature_grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #F0ECE4;
        }
        .stamp_box {
            display: inline-block;
            border: 2px dashed #2E7D32;
            color: #2E7D32;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            margin-top: 16px;
            letter-spacing: 0.05em;
        }
        .action_toolbar {
            max-width: 800px;
            margin: 0 auto 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            background: #8C6B37;
            color: #FFFFFF;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        @media print {
            body {
                background: #FFFFFF;
                padding: 0;
            }
            .action_toolbar {
                display: none !important;
            }
            .invoice_container {
                box-shadow: none;
                padding: 0;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>

    <div class="action_toolbar">
        <a href="{{ route('admin.invoices.show', $invoice->id) }}" style="color: #5A554E; text-decoration: none; font-size: 13px;">
            ← Quay lại trang quản trị
        </a>
        <button type="button" onclick="window.print()" class="btn">
            🖨 In Hóa Đơn / Lưu PDF (Ctrl + P)
        </button>
    </div>

    <div class="invoice_container">
        <!-- Header -->
        <div class="invoice_header">
            <div>
                <div class="brand_title">MÂY HẸN ATELIER</div>
                <div class="brand_tagline">Nền Tảng Thiệp Cưới & Lưu Trữ Kỷ Niệm Trực Tuyến</div>
                <div class="company_info">
                    <strong>CÔNG TY TNHH MÂY HẸN ATELIER</strong><br>
                    Website: mayhen.vn | Hòm thư: contact@mayhen.vn<br>
                    Hotline CSKH: 0988.776.xxx | Mã số thuế: 0109888888
                </div>
            </div>
            <div class="invoice_meta">
                <div class="invoice_title">HÓA ĐƠN ĐIỆN TỬ</div>
                <div class="invoice_number">Số: {{ $invoice->invoice_number ?? 'DỰ THẢO' }}</div>
                <div style="font-size: 12px; color: #7A756D; margin-top: 4px;">
                    Ngày: {{ $invoice->issued_at ? $invoice->issued_at->format('d/m/Y') : date('d/m/Y') }}
                </div>
                <div style="font-size: 12px; color: #7A756D;">
                    Mã đơn: #{{ $invoice->order->order_code ?? $invoice->order_id }}
                </div>
            </div>
        </div>

        <!-- Bên Mua & Bên Bán -->
        <div class="party_grid">
            <div class="party_box">
                <div class="party_title">Đơn Vị Bán Hàng</div>
                <div class="party_row"><strong>Đơn vị:</strong> CÔNG TY TNHH MÂY HẸN ATELIER</div>
                <div class="party_row"><strong>Mã số thuế:</strong> 0109888888</div>
                <div class="party_row"><strong>Địa chỉ:</strong> Hà Nội, Việt Nam</div>
                <div class="party_row"><strong>Tài khoản:</strong> MBBank (Ngân hàng TMCP Quân Đội)</div>
            </div>
            <div class="party_box">
                <div class="party_title">Đơn Vị / Khách Mua Hàng</div>
                <div class="party_row"><strong>Khách hàng:</strong> {{ $invoice->customer_name }}</div>
                @if($invoice->company_name)
                    <div class="party_row"><strong>Công ty:</strong> {{ $invoice->company_name }}</div>
                @endif
                @if($invoice->tax_id)
                    <div class="party_row"><strong>Mã số thuế:</strong> {{ $invoice->tax_id }}</div>
                @endif
                @if($invoice->company_address)
                    <div class="party_row"><strong>Địa chỉ:</strong> {{ $invoice->company_address }}</div>
                @endif
                <div class="party_row"><strong>Email:</strong> {{ $invoice->customer_email }}</div>
            </div>
        </div>

        <!-- Bảng Dịch Vụ -->
        <table class="invoice_table">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">STT</th>
                    <th>Tên Gói Dịch Vụ / Hàng Hóa</th>
                    <th style="width: 60px; text-align: center;">ĐVT</th>
                    <th style="width: 50px; text-align: center;">SL</th>
                    <th style="width: 120px; text-align: right;">Đơn Giá</th>
                    <th style="width: 130px; text-align: right;">Thành Tiền</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">1</td>
                    <td>
                        <strong>{{ $invoice->order->plan->name ?? 'Gói Dịch Vụ Thiệp Cưới Mây Hẹn' }}</strong><br>
                        <span style="font-size: 11px; color: #7A756D;">Lưu trữ thiệp cưới online cao cấp, quản lý khách mời RSVP & xuất trọn bộ kỷ niệm.</span>
                    </td>
                    <td style="text-align: center;">Gói</td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right; font-family: monospace;">{{ $invoice->formatted_subtotal }}</td>
                    <td style="text-align: right; font-family: monospace; font-weight: 600;">{{ $invoice->formatted_subtotal }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Tổng Kết Doanh Thu -->
        <div class="summary_grid">
            <div class="summary_box">
                <div class="summary_row">
                    <span>Cộng tiền hàng (Subtotal):</span>
                    <span style="font-family: monospace; font-weight: 600;">{{ $invoice->formatted_subtotal }}</span>
                </div>
                <div class="summary_row">
                    <span>Thuế suất GTGT ({{ $invoice->vat_rate }}%):</span>
                    <span style="font-family: monospace;">{{ $invoice->formatted_vat }}</span>
                </div>
                <div class="summary_row summary_total">
                    <span>Tổng tiền thanh toán:</span>
                    <span style="font-family: monospace;">{{ $invoice->formatted_total }}</span>
                </div>
            </div>
        </div>

        <!-- Chữ Ký & Dấu Mộc Điện Tử -->
        <div class="signature_grid">
            <div>
                <div style="font-weight: 600; color: #5A554E;">NGƯỜI MUA HÀNG</div>
                <div style="font-size: 11px; color: #7A756D; margin-top: 4px;">(Ký, ghi rõ họ tên)</div>
                <div style="margin-top: 48px; font-weight: 600;">{{ $invoice->customer_name }}</div>
            </div>
            <div>
                <div style="font-weight: 600; color: #5A554E;">ĐƠN VỊ BÁN HÀNG</div>
                <div style="font-size: 11px; color: #7A756D; margin-top: 4px;">(Ký điện tử & Đóng dấu số)</div>
                <div style="margin-top: 16px;">
                    <div class="stamp_box">
                        ✓ XÁC THỰC KÝ SỐ ĐIỆN TỬ<br>
                        MÂY HẸN ATELIER CO., LTD<br>
                        {{ $invoice->issued_at ? $invoice->issued_at->format('d/m/Y H:i') : date('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

