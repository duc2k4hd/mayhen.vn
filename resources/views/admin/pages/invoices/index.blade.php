@extends('admin.layouts.master')

@section('title', 'Quản Trị Hóa Đơn & Phiếu Thu VAT')
@section('page_title', 'Hóa Đơn Điện Tử, Phiếu Thu VAT & Chứng Từ Tài Chính (Billing Cockpit)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/settings.css') }}">
    <style>
        .mayhen_admin_kpi_grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        @media (max-width: 992px) {
            .mayhen_admin_kpi_grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .mayhen_admin_kpi_grid {
                grid-template-columns: 1fr;
            }
        }
        .mayhen_admin_kpi_card {
            background: #FFFFFF;
            border-radius: 12px;
            border: 1px solid #EBE7DF;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }
        .mayhen_admin_kpi_title {
            font-size: 0.8125rem;
            color: var(--mh-admin-muted, #7A756D);
            margin-bottom: 8px;
            font-weight: 500;
        }
        .mayhen_admin_kpi_value {
            font-size: 1.65rem;
            font-weight: 600;
            color: var(--mh-admin-charcoal, #2B2927);
        }
        .mayhen_admin_kpi_meta {
            font-size: 0.75rem;
            color: #8C6B37;
            margin-top: 6px;
        }
    </style>
@endpush

@section('content')
    <!-- Intro Card -->
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 20px 24px; border-left: 4px solid var(--mh-admin-primary, #A38048);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h3 style="font-size: 1.25rem; margin-bottom: 4px; color: var(--mh-admin-charcoal, #2B2927);">
                    Phân Hệ Hóa Đơn Điện Tử & Phiếu Thu VAT (Mục 141 PRD V3 & Mục 9.4 DOCX)
                </h3>
                <p style="color: var(--mh-admin-muted, #7A756D); font-size: 0.84375rem; margin: 0;">
                    Quản lý xuất hóa đơn công ty (Tên doanh nghiệp, MST, Địa chỉ) và phiếu thu điện tử cho các cặp đôi thanh toán gói dịch vụ thiệp cưới Mây Hẹn.
                </p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <span class="mayhen_admin_badge" style="background: #E8F5E9; color: #2E7D32; padding: 6px 12px; font-size: 0.75rem; font-weight: 600;">
                    📜 HĐĐT Tự Động Tăng
                </span>
                <span class="mayhen_admin_badge" style="background: #E1F5FE; color: #0277BD; padding: 6px 12px; font-size: 0.75rem; font-weight: 600;">
                    🖨 In Phiếu Thu A4
                </span>
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

    <!-- 4 Thẻ KPI Doanh Thu Hóa Đơn -->
    <div class="mayhen_admin_kpi_grid">
        <div class="mayhen_admin_kpi_card">
            <div class="mayhen_admin_kpi_title">Tổng Số Hóa Đơn</div>
            <div class="mayhen_admin_kpi_value">{{ number_format($kpis['total_invoices']) }}</div>
            <div class="mayhen_admin_kpi_meta">Yêu cầu lập hóa đơn trong hệ thống</div>
        </div>
        <div class="mayhen_admin_kpi_card">
            <div class="mayhen_admin_kpi_title">Đã Phát Hành Hợp Lệ</div>
            <div class="mayhen_admin_kpi_value" style="color: #2E7D32;">
                {{ number_format($kpis['issued_invoices']) }}
            </div>
            <div class="mayhen_admin_kpi_meta">Hóa đơn có mã số chính thức</div>
        </div>
        <div class="mayhen_admin_kpi_card">
            <div class="mayhen_admin_kpi_title">Chờ Duyệt Phát Hành</div>
            <div class="mayhen_admin_kpi_value" style="color: #F57F17;">
                {{ number_format($kpis['pending_invoices']) }}
            </div>
            <div class="mayhen_admin_kpi_meta">Cần kế toán kiểm tra xuất HĐ</div>
        </div>
        <div class="mayhen_admin_kpi_card">
            <div class="mayhen_admin_kpi_title">Tổng Doanh Thu Đã Xuất HĐ</div>
            <div class="mayhen_admin_kpi_value" style="font-size: 1.35rem; color: #8C6B37;">
                {{ number_format($kpis['total_issued_amount'], 0, ',', '.') }} đ
            </div>
            <div class="mayhen_admin_kpi_meta">Thuế VAT: {{ number_format($kpis['total_vat_amount'], 0, ',', '.') }} đ</div>
        </div>
    </div>

    <!-- Thanh Tìm Kiếm & Lọc Trạng Thái -->
    <div class="mayhen_admin_card" style="padding: 20px 24px; margin-bottom: 24px;">
        <form method="GET" action="{{ route('admin.invoices.index') }}" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; gap: 12px; align-items: center; flex: 1; flex-wrap: wrap;">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       class="mayhen_admin_input" 
                       placeholder="Tìm theo số HĐ, tên công ty, MST, mã đơn hàng..." 
                       style="min-width: 260px; max-width: 380px;">

                <select name="status" class="mayhen_admin_select" onchange="this.form.submit()">
                    <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>Tất cả trạng thái</option>
                    <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Đã phát hành</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ phát hành</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>

                <button type="submit" class="mayhen_admin_btn" style="background: #F4F1EA; border: 1px solid #D6D0C4; color: #5A554E; padding: 9px 16px; border-radius: 6px; font-size: 0.8125rem;">
                    Lọc Dữ Liệu
                </button>
            </div>

            <div style="display: flex; gap: 10px;">
                <a href="{{ route('admin.invoices.create') }}" class="mayhen_admin_btn mayhen_admin_btn_primary" style="background: var(--mh-admin-primary, #A38048); color: #FFFFFF; padding: 9px 18px; border-radius: 6px; text-decoration: none; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Lập Hóa Đơn Mới
                </a>
                <a href="{{ route('admin.invoices.export') }}" class="mayhen_admin_btn" style="background: #FAF8F5; border: 1px solid #D6D0C4; color: var(--mh-admin-charcoal, #2B2927); padding: 9px 18px; border-radius: 6px; text-decoration: none; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Xuất CSV (UTF-8)
                </a>
            </div>
        </form>
    </div>

    <!-- Bảng Danh Sách Hóa Đơn -->
    <div class="mayhen_admin_card" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h4 style="font-size: 1.15rem; margin: 0; color: var(--mh-admin-charcoal, #2B2927);">
                    Sổ Quản Lý Hóa Đơn & Chứng Từ Thu Tiền
                </h4>
                <p style="color: var(--mh-admin-muted, #7A756D); font-size: 0.8125rem; margin: 2px 0 0;">
                    Nhấn vào mã số hóa đơn để xem chi tiết, in phiếu thu A4 hoặc duyệt phát hành chính thức.
                </p>
            </div>
        </div>

        @if($invoices->isEmpty())
            <div style="padding: 48px; text-align: center; color: var(--mh-admin-muted, #7A756D); font-size: 0.875rem;">
                Không có hóa đơn nào trong danh sách.
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="mayhen_admin_table" style="width: 100%; font-size: 0.8125rem;">
                    <thead>
                        <tr>
                            <th style="width: 140px;">Số Hóa Đơn</th>
                            <th style="width: 130px;">Mã Đơn Hàng</th>
                            <th>Khách Hàng & Doanh Nghiệp</th>
                            <th style="width: 140px;">Tổng Thanh Toán</th>
                            <th style="width: 140px;">Ngày Phát Hành</th>
                            <th style="width: 140px;">Trạng Thái</th>
                            <th style="width: 180px; text-align: right;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $inv)
                            <tr>
                                <td>
                                    @if($inv->invoice_number)
                                        <a href="{{ route('admin.invoices.show', $inv->id) }}" style="font-weight: 600; color: #8C6B37; text-decoration: none;">
                                            {{ $inv->invoice_number }}
                                        </a>
                                    @else
                                        <span style="color: var(--mh-admin-muted, #7A756D); font-style: italic;">Chờ cấp số</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $inv->order_id) }}" style="color: var(--mh-admin-charcoal, #2B2927); font-weight: 500;">
                                        #{{ $inv->order->order_code ?? $inv->order_id }}
                                    </a>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--mh-admin-charcoal, #2B2927);">
                                        {{ $inv->customer_name }}
                                    </div>
                                    @if($inv->company_name)
                                        <div style="color: #5A554E; font-size: 0.75rem; margin-top: 2px;">
                                            🏢 {{ $inv->company_name }}
                                        </div>
                                    @endif
                                    @if($inv->tax_id)
                                        <div style="color: var(--mh-admin-muted, #7A756D); font-size: 0.7rem;">
                                            MST: {{ $inv->tax_id }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--mh-admin-charcoal, #2B2927);">
                                        {{ $inv->formatted_total }}
                                    </div>
                                    <div style="color: var(--mh-admin-muted, #7A756D); font-size: 0.7rem;">
                                        (VAT: {{ $inv->vat_rate }}%)
                                    </div>
                                </td>
                                <td style="color: var(--mh-admin-muted, #7A756D); white-space: nowrap;">
                                    {{ $inv->issued_at ? $inv->issued_at->format('d/m/Y H:i') : 'Chưa phát hành' }}
                                </td>
                                <td>
                                    <span class="mayhen_admin_badge" style="background: {{ $inv->status_color }}15; color: {{ $inv->status_color }}; font-weight: 600; font-size: 0.75rem; padding: 4px 10px; border-radius: 12px; border: 1px solid {{ $inv->status_color }}30;">
                                        {{ $inv->status_label }}
                                    </span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <a href="{{ route('admin.invoices.show', $inv->id) }}" class="mayhen_admin_btn" style="background: #FAF8F5; border: 1px solid #D6D0C4; color: var(--mh-admin-charcoal, #2B2927); font-size: 0.75rem; padding: 5px 8px; border-radius: 4px; text-decoration: none;">
                                        Chi Tiết
                                    </a>

                                    <a href="{{ route('admin.invoices.print', $inv->id) }}" target="_blank" class="mayhen_admin_btn" style="background: #FAF8F5; border: 1px solid #D6D0C4; color: #0277BD; font-size: 0.75rem; padding: 5px 8px; border-radius: 4px; text-decoration: none; margin-left: 2px;">
                                        🖨 In
                                    </a>

                                    @if($inv->isPending())
                                        <form action="{{ route('admin.invoices.issue', $inv->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Xác nhận phát hành hóa đơn chính thức cho khách hàng?');">
                                            @csrf
                                            <button type="submit" class="mayhen_admin_btn" style="background: #E8F5E9; border: 1px solid #A5D6A7; color: #2E7D32; font-size: 0.75rem; padding: 5px 8px; border-radius: 4px; cursor: pointer; margin-left: 2px;">
                                                ✓ Phát Hành
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px;">
                {{ $invoices->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection

