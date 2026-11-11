@extends('admin.layouts.master')

@section('title', 'Chi Tiết Hóa Đơn #' . ($invoice->invoice_number ?? $invoice->id))
@section('page_title', 'Chi Tiết Hóa Đơn Điện Tử #' . ($invoice->invoice_number ?? $invoice->id))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/settings.css') }}">
@endpush

@section('content')
    <!-- Header Card -->
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 20px 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <a href="{{ route('admin.invoices.index') }}" style="color: #8C6B37; text-decoration: none; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 4px; margin-bottom: 6px;">
                    ← Quay lại danh sách hóa đơn
                </a>
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; margin: 0; color: var(--mh-admin-charcoal, #2B2927);">
                    Hóa Đơn Điện Tử: {{ $invoice->invoice_number ?? 'Dự Thảo (Chưa cấp số)' }}
                </h3>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <span class="mayhen_admin_badge" style="background: {{ $invoice->status_color }}15; color: {{ $invoice->status_color }}; font-weight: 600; font-size: 0.8125rem; padding: 6px 14px; border-radius: 12px; border: 1px solid {{ $invoice->status_color }}30;">
                    {{ $invoice->status_label }}
                </span>
                <a href="{{ route('admin.invoices.print', $invoice->id) }}" target="_blank" class="mayhen_admin_btn" style="background: #FAF8F5; border: 1px solid #D6D0C4; color: #0277BD; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.84375rem; display: inline-flex; align-items: center; gap: 6px;">
                    🖨 In Phiếu Thu / Xuất PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Thông báo Session -->
    @if(session('success'))
        <div class="mayhen_admin_alert mayhen_admin_alert_success" style="margin-bottom: 20px; padding: 12px 18px; border-radius: 8px; background: #E8F5E9; color: #1B5E20; border: 1px solid #A5D6A7;">
            <strong>✓ Thành công:</strong> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mayhen_admin_alert mayhen_admin_alert_danger" style="margin-bottom: 20px; padding: 12px 18px; border-radius: 8px; background: #FFEBEE; color: #B71C1C; border: 1px solid #FFCDD2;">
            <strong>✕ Lỗi:</strong> {{ session('error') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
        <!-- Cột Trái: Bảng Chi Tiết Hóa Đơn -->
        <div>
            <!-- Thông tin doanh nghiệp & khách hàng -->
            <div class="mayhen_admin_card" style="padding: 24px; margin-bottom: 24px;">
                <h4 style="font-family: 'Playfair Display', serif; font-size: 1.1rem; margin: 0 0 16px; color: var(--mh-admin-charcoal, #2B2927); border-bottom: 1px solid #F0ECE4; padding-bottom: 10px;">
                    Thông Tin Đơn Vị Mua Hàng
                </h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 0.84375rem;">
                    <div>
                        <div style="color: var(--mh-admin-muted, #7A756D); margin-bottom: 2px;">Tên khách hàng / Người đại diện:</div>
                        <div style="font-weight: 600; color: var(--mh-admin-charcoal, #2B2927);">{{ $invoice->customer_name }}</div>
                    </div>
                    <div>
                        <div style="color: var(--mh-admin-muted, #7A756D); margin-bottom: 2px;">Email nhận HĐĐT:</div>
                        <div style="font-weight: 500;">{{ $invoice->customer_email }}</div>
                    </div>
                    <div>
                        <div style="color: var(--mh-admin-muted, #7A756D); margin-bottom: 2px;">Tên doanh nghiệp / Đơn vị:</div>
                        <div style="font-weight: 600; color: #8C6B37;">{{ $invoice->company_name ?: '(Cá nhân mua hàng)' }}</div>
                    </div>
                    <div>
                        <div style="color: var(--mh-admin-muted, #7A756D); margin-bottom: 2px;">Mã số thuế (MST):</div>
                        <div style="font-family: monospace; font-weight: 600;">{{ $invoice->tax_id ?: 'Chưa cung cấp' }}</div>
                    </div>
                    <div style="grid-column: span 2;">
                        <div style="color: var(--mh-admin-muted, #7A756D); margin-bottom: 2px;">Địa chỉ xuất hóa đơn:</div>
                        <div>{{ $invoice->company_address ?: 'Chưa cung cấp' }}</div>
                    </div>
                </div>
            </div>

            <!-- Bảng Dòng Sản Phẩm / Gói Cước -->
            <div class="mayhen_admin_card" style="padding: 24px; margin-bottom: 24px;">
                <h4 style="font-family: 'Playfair Display', serif; font-size: 1.1rem; margin: 0 0 16px; color: var(--mh-admin-charcoal, #2B2927); border-bottom: 1px solid #F0ECE4; padding-bottom: 10px;">
                    Chi Tiết Dịch Vụ Thanh Toán
                </h4>
                <table class="mayhen_admin_table" style="width: 100%; font-size: 0.84375rem;">
                    <thead>
                        <tr>
                            <th>Tên Dịch Vụ / Gói Cước</th>
                            <th style="width: 80px; text-align: center;">ĐVT</th>
                            <th style="width: 120px; text-align: right;">Đơn Giá</th>
                            <th style="width: 130px; text-align: right;">Thành Tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: var(--mh-admin-charcoal, #2B2927);">
                                    Gói Dịch Vụ Thiệp Cưới: {{ $invoice->order->plan->name ?? 'Gói Cước Mây Hẹn' }}
                                </div>
                                <div style="color: var(--mh-admin-muted, #7A756D); font-size: 0.75rem; margin-top: 2px;">
                                    Đơn hàng gốc: #{{ $invoice->order->order_code ?? $invoice->order_id }} — Hình thức: {{ strtoupper($invoice->payment_method) }}
                                </div>
                            </td>
                            <td style="text-align: center;">Gói</td>
                            <td style="text-align: right; font-family: monospace;">{{ $invoice->formatted_subtotal }}</td>
                            <td style="text-align: right; font-family: monospace; font-weight: 600;">{{ $invoice->formatted_subtotal }}</td>
                        </tr>
                    </tbody>
                </table>

                <div style="margin-top: 20px; border-top: 1px solid #F0ECE4; padding-top: 16px; display: flex; justify-content: flex-end;">
                    <div style="width: 260px; font-size: 0.84375rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: var(--mh-admin-muted, #7A756D);">Cộng tiền hàng:</span>
                            <span style="font-family: monospace; font-weight: 500;">{{ $invoice->formatted_subtotal }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: var(--mh-admin-muted, #7A756D);">Thuế suất GTGT ({{ $invoice->vat_rate }}%):</span>
                            <span style="font-family: monospace; font-weight: 500;">{{ $invoice->formatted_vat }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 1rem; font-weight: 700; color: #8C6B37; border-top: 1px dashed #D6D0C4; padding-top: 8px;">
                            <span>Tổng thanh toán:</span>
                            <span style="font-family: monospace;">{{ $invoice->formatted_total }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột Phải: Trạng Thái & Thao Tác Quản Trị -->
        <div>
            <div class="mayhen_admin_card" style="padding: 24px; margin-bottom: 24px;">
                <h4 style="font-family: 'Playfair Display', serif; font-size: 1.1rem; margin: 0 0 16px; color: var(--mh-admin-charcoal, #2B2927);">
                    Hành Động Quản Trị
                </h4>

                @if($invoice->isPending())
                    <form action="{{ route('admin.invoices.issue', $invoice->id) }}" method="POST" onsubmit="return confirm('Xác nhận phát hành hóa đơn chính thức và gửi email thông báo cho khách hàng?');" style="margin-bottom: 12px;">
                        @csrf
                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="width: 100%; background: #2E7D32; color: #FFFFFF; border: none; padding: 10px; border-radius: 6px; font-weight: 500; font-size: 0.875rem; cursor: pointer;">
                            ✓ Phát Hành Hóa Đơn Điện Tử
                        </button>
                    </form>
                @endif

                @if(!$invoice->isCancelled())
                    <form action="{{ route('admin.invoices.cancel', $invoice->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy hóa đơn này? Hành động này sẽ được ghi vào nhật ký kiểm toán.');">
                        @csrf
                        <div style="margin-bottom: 10px;">
                            <input type="text" name="reason" class="mayhen_admin_input" style="width: 100%; font-size: 0.8125rem;" placeholder="Lý do hủy hóa đơn..." required>
                        </div>
                        <button type="submit" class="mayhen_admin_btn" style="width: 100%; background: #FFEBEE; border: 1px solid #FFCDD2; color: #C62828; padding: 9px; border-radius: 6px; font-size: 0.84375rem; cursor: pointer;">
                            ✕ Hủy Hóa Đơn Này
                        </button>
                    </form>
                @else
                    <div style="padding: 12px; background: #FFEBEE; border-radius: 6px; color: #C62828; font-size: 0.8125rem;">
                        Hóa đơn này đã bị hủy.
                    </div>
                @endif

                <div style="margin-top: 20px; border-top: 1px solid #F0ECE4; padding-top: 14px; font-size: 0.78125rem; color: var(--mh-admin-muted, #7A756D);">
                    <div><strong>Ngày tạo:</strong> {{ $invoice->created_at->format('d/m/Y H:i:s') }}</div>
                    @if($invoice->issued_at)
                        <div style="margin-top: 4px;"><strong>Ngày phát hành:</strong> {{ $invoice->issued_at->format('d/m/Y H:i:s') }}</div>
                        <div style="margin-top: 4px;"><strong>Người phát hành:</strong> {{ $invoice->issuer->name ?? 'Hệ thống' }}</div>
                    @endif
                </div>
            </div>

            @if($invoice->notes)
                <div class="mayhen_admin_card" style="padding: 20px;">
                    <div style="font-weight: 600; font-size: 0.8125rem; color: var(--mh-admin-charcoal, #2B2927); margin-bottom: 6px;">
                        Ghi Chú Chứng Từ:
                    </div>
                    <div style="font-size: 0.8125rem; color: #5A554E; white-space: pre-wrap; line-height: 1.5;">
                        {{ $invoice->notes }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

