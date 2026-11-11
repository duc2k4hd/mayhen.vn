@extends('admin.layouts.master')

@section('title', 'Sổ Đối Soát Giao Dịch & Webhook Tài Chính')

@section('content')
<div class="mayhen_admin_page_header">
    <div class="mayhen_admin_page_header_left">
        <h1 class="mayhen_admin_page_title">Sổ Đối Soát Giao Dịch & Webhook Tài Chính</h1>
        <p class="mayhen_admin_page_desc">
            Quy trình đối soát ngân hàng 2 bước (Maker-Checker), giải quyết sai lệch số tiền (PAY-03) và nhật ký webhook thanh toán theo Mục 137 PRD V3.
        </p>
    </div>
    <div class="mayhen_admin_page_header_actions">
        <a href="{{ route('admin.reconciliations.export', request()->only('status')) }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Xuất Báo Cáo CSV
        </a>

        @if($currentTab === 'reconciliations')
            <button type="button" class="mayhen_admin_btn mayhen_admin_btn_primary" onclick="openModal('createReconciliationModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Lập Phiếu Đối Soát Mới
            </button>
        @endif
    </div>
</div>

{{-- 4 Thẻ KPI Chỉ Số Đối Soát --}}
<div class="mayhen_admin_kpi_grid" style="margin-bottom: 24px;">
    <div class="mayhen_admin_kpi_card">
        <div class="mayhen_admin_kpi_icon" style="background: rgba(217, 131, 36, 0.12); color: #B07D3B;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div class="mayhen_admin_kpi_content">
            <div class="mayhen_admin_kpi_label">Hồ Sơ Chờ Đối Soát</div>
            <div class="mayhen_admin_kpi_value" style="color: {{ $kpis['pending_cases_count'] > 0 ? '#B07D3B' : 'inherit' }};">
                {{ number_format($kpis['pending_cases_count']) }}
            </div>
            <div class="mayhen_admin_kpi_sub">Chờ Kế toán trưởng duyệt</div>
        </div>
    </div>

    <div class="mayhen_admin_kpi_card">
        <div class="mayhen_admin_kpi_icon" style="background: rgba(217, 83, 79, 0.12); color: #C53030;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
            </svg>
        </div>
        <div class="mayhen_admin_kpi_content">
            <div class="mayhen_admin_kpi_label">Sai Lệch Số Tiền (PAY-03)</div>
            <div class="mayhen_admin_kpi_value" style="color: {{ $kpis['amount_mismatch_count'] > 0 ? '#C53030' : 'inherit' }};">
                {{ number_format($kpis['amount_mismatch_count']) }}
            </div>
            <div class="mayhen_admin_kpi_sub">Chuyển thiếu/thừa cần xử lý</div>
        </div>
    </div>

    <div class="mayhen_admin_kpi_card">
        <div class="mayhen_admin_kpi_icon" style="background: rgba(74, 144, 226, 0.12); color: #2B6CB0;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3z"></path>
            </svg>
        </div>
        <div class="mayhen_admin_kpi_content">
            <div class="mayhen_admin_kpi_label">Tiền Hoàn Chờ Duyệt</div>
            <div class="mayhen_admin_kpi_value">{{ number_format($kpis['refund_pending_amount']) }} đ</div>
            <div class="mayhen_admin_kpi_sub">Yêu cầu hoàn trả tài chính</div>
        </div>
    </div>

    <div class="mayhen_admin_kpi_card">
        <div class="mayhen_admin_kpi_icon" style="background: rgba(45, 106, 79, 0.12); color: #2D6A4F;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <div class="mayhen_admin_kpi_content">
            <div class="mayhen_admin_kpi_label">Đã Khớp Tháng Này</div>
            <div class="mayhen_admin_kpi_value" style="color: #2D6A4F;">
                {{ number_format($kpis['reconciled_this_month_count']) }}
            </div>
            <div class="mayhen_admin_kpi_sub">Giao dịch đã kiểm tra hợp lệ</div>
        </div>
    </div>
</div>

{{-- Navigation Tabs --}}
<div class="mayhen_admin_tabs" style="display: flex; gap: 8px; border-bottom: 1px solid #EFE8DD; margin-bottom: 24px;">
    <a href="{{ route('admin.reconciliations.index', ['tab' => 'reconciliations']) }}" 
       class="mayhen_admin_tab {{ $currentTab === 'reconciliations' ? 'active' : '' }}"
       style="padding: 10px 18px; font-size: 14px; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'reconciliations' ? '#C5A880' : 'transparent' }}; color: {{ $currentTab === 'reconciliations' ? '#1F1D1A' : '#6c757d' }};">
        ⚖️ Sổ Đối Soát & Khớp Lệnh
    </a>
    <a href="{{ route('admin.reconciliations.index', ['tab' => 'webhook-logs']) }}" 
       class="mayhen_admin_tab {{ $currentTab === 'webhook-logs' ? 'active' : '' }}"
       style="padding: 10px 18px; font-size: 14px; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'webhook-logs' ? '#C5A880' : 'transparent' }}; color: {{ $currentTab === 'webhook-logs' ? '#1F1D1A' : '#6c757d' }};">
        📡 Nhật Ký Webhook Ngân Hàng Inbound
    </a>
    <a href="{{ route('admin.reconciliations.index', ['tab' => 'refunds']) }}" 
       class="mayhen_admin_tab {{ $currentTab === 'refunds' ? 'active' : '' }}"
       style="padding: 10px 18px; font-size: 14px; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'refunds' ? '#C5A880' : 'transparent' }}; color: {{ $currentTab === 'refunds' ? '#1F1D1A' : '#6c757d' }};">
        🔄 Sổ Quản Lý Hoàn Tiền (Refunds)
    </a>
</div>

{{-- TAB 1: SỔ ĐỐI SOÁT & KHỚP LỆNH --}}
@if($currentTab === 'reconciliations')
    <div class="mayhen_admin_card">
        {{-- Bộ lọc --}}
        <div style="padding: 16px 20px; border-bottom: 1px solid #EFE8DD;">
            <form action="{{ route('admin.reconciliations.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="tab" value="reconciliations">
                
                <div style="flex: 1; min-width: 240px;">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo mã đơn hàng, mã giao dịch, tên chủ tài khoản..." 
                           class="mayhen_admin_form_input" style="height: 38px;">
                </div>

                <div style="width: 170px;">
                    <select name="action_type" class="mayhen_admin_form_select" style="height: 38px;">
                        <option value="">Tất cả nghiệp vụ</option>
                        <option value="manual_approval" {{ request('action_type') === 'manual_approval' ? 'selected' : '' }}>Khớp lệnh thủ công</option>
                        <option value="amount_mismatch_resolution" {{ request('action_type') === 'amount_mismatch_resolution' ? 'selected' : '' }}>Lệch số tiền (PAY-03)</option>
                    </select>
                </div>

                <div style="width: 160px;">
                    <select name="status" class="mayhen_admin_form_select" style="height: 38px;">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã phê duyệt</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Bị từ chối</option>
                    </select>
                </div>

                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_secondary" style="height: 38px;">
                    Lọc Sổ
                </button>

                @if(request()->anyFilled(['search', 'action_type', 'status']))
                    <a href="{{ route('admin.reconciliations.index', ['tab' => 'reconciliations']) }}" class="mayhen_admin_btn mayhen_admin_btn_outline" style="height: 38px;">
                        Xóa lọc
                    </a>
                @endif
            </form>
        </div>

        {{-- Bảng danh sách phiếu đối soát --}}
        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th style="width: 140px;">Mã Đơn Hàng</th>
                        <th style="width: 170px;">Loại Nghiệp Vụ</th>
                        <th style="width: 130px;">Số Tiền Thực</th>
                        <th style="width: 180px;">Mã GD Ngân Hàng</th>
                        <th>Thông Tin Tài Khoản</th>
                        <th style="width: 140px;">Người Lập</th>
                        <th style="width: 130px;">Trạng Thái</th>
                        <th style="width: 140px; text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reconciliations as $rec)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #1F1D1A; font-size: 13px;">
                                    {{ $rec->order?->order_code ?? 'N/A' }}
                                </div>
                                <div style="font-size: 11px; color: #8A8175;">
                                    Gốc: {{ number_format($rec->order?->total_amount ?? 0) }} đ
                                </div>
                            </td>
                            <td>
                                <span class="mayhen_admin_badge mayhen_admin_badge_info" style="font-size: 11px;">
                                    {{ $rec->action_label }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #1F1D1A; font-size: 13px;">
                                    {{ $rec->formatted_amount }}
                                </div>
                                @if($rec->order && $rec->amount !== (int)$rec->order->total_amount)
                                    <div style="font-size: 10px; color: #C53030; font-weight: 600;">
                                        Lệch {{ number_format(abs($rec->amount - (int)$rec->order->total_amount)) }} đ
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div style="font-family: monospace; font-size: 13px; font-weight: 600; color: #2B6CB0;">
                                    {{ $rec->reference_code }}
                                </div>
                                <div style="font-size: 11px; color: #8A8175;">{{ $rec->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                @if($rec->bank_name || $rec->bank_account_holder)
                                    <div style="font-size: 12px; font-weight: 500; color: #1F1D1A;">{{ $rec->bank_account_holder ?? 'N/A' }}</div>
                                    <div style="font-size: 11px; color: #8A8175;">{{ $rec->bank_name }} - {{ $rec->bank_account_number }}</div>
                                @else
                                    <span style="font-size: 12px; color: #8A8175; font-style: italic;">Chuyển khoản trực tiếp</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 12px; color: #1F1D1A;">{{ $rec->creator?->name ?? 'Hệ thống' }}</div>
                                @if($rec->approver)
                                    <div style="font-size: 11px; color: #2D6A4F;">Duyệt: {{ $rec->approver->name }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="mayhen_admin_badge mayhen_admin_badge_{{ $rec->status_badge_color }}">
                                    {{ $rec->status_label }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                @if($rec->isPending())
                                    <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                        {{-- Nút Phê Duyệt --}}
                                        <form action="{{ route('admin.reconciliations.approve', $rec->id) }}" method="POST" onsubmit="return confirm('Xác nhận phê duyệt đối soát đơn hàng này? Quyền gói cước sẽ được cấp tự động cho khách.');">
                                            @csrf
                                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="padding: 4px 8px; font-size: 12px;" title="Phê duyệt đối soát">
                                                Duyệt
                                            </button>
                                        </form>

                                        {{-- Nút Từ Chối --}}
                                        <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" style="padding: 4px 8px; font-size: 12px; color: #e53e3e;" 
                                                onclick="openRejectModal({{ $rec->id }})" title="Từ chối đối soát">
                                            Từ chối
                                        </button>
                                    </div>
                                @else
                                    <span style="font-size: 11px; color: #8A8175;">{{ $rec->reconciled_at?->format('d/m/Y') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 48px; color: #8A8175;">
                                <p style="margin: 0; font-size: 14px;">Chưa có phiếu đối soát nào theo bộ lọc.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reconciliations->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #EFE8DD;">
                {{ $reconciliations->links() }}
            </div>
        @endif
    </div>
@endif

{{-- TAB 2: NHẬT KÝ WEBHOOK INBOUND --}}
@if($currentTab === 'webhook-logs')
    <div class="mayhen_admin_card">
        {{-- Bộ lọc Webhook Logs --}}
        <div style="padding: 16px 20px; border-bottom: 1px solid #EFE8DD;">
            <form action="{{ route('admin.reconciliations.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="tab" value="webhook-logs">
                
                <div style="flex: 1; min-width: 240px;">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo mã giao dịch ngân hàng hoặc nội dung chuyển khoản..." 
                           class="mayhen_admin_form_input" style="height: 38px;">
                </div>

                <div style="width: 170px;">
                    <select name="gateway" class="mayhen_admin_form_select" style="height: 38px;">
                        <option value="">Tất cả cổng</option>
                        <option value="sepay" {{ request('gateway') === 'sepay' ? 'selected' : '' }}>SePay Bank Hub</option>
                        <option value="vietqr" {{ request('gateway') === 'vietqr' ? 'selected' : '' }}>VietQR Direct</option>
                        <option value="bank_transfer" {{ request('gateway') === 'bank_transfer' ? 'selected' : '' }}>Chuyển khoản thủ công</option>
                    </select>
                </div>

                <div style="width: 170px;">
                    <select name="status" class="mayhen_admin_form_select" style="height: 38px;">
                        <option value="">Tất cả trạng thái</option>
                        <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Hợp lệ (Khớp)</option>
                        <option value="mismatch" {{ request('status') === 'mismatch' ? 'selected' : '' }}>Lệch số tiền (PAY-03)</option>
                        <option value="duplicate" {{ request('status') === 'duplicate' ? 'selected' : '' }}>Gửi trùng (Idempotent)</option>
                    </select>
                </div>

                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_secondary" style="height: 38px;">
                    Lọc Nhật Ký
                </button>

                @if(request()->anyFilled(['search', 'gateway', 'status']))
                    <a href="{{ route('admin.reconciliations.index', ['tab' => 'webhook-logs']) }}" class="mayhen_admin_btn mayhen_admin_btn_outline" style="height: 38px;">
                        Xóa lọc
                    </a>
                @endif
            </form>
        </div>

        {{-- Bảng Webhook Logs --}}
        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th style="width: 160px;">Thời Gian</th>
                        <th style="width: 140px;">Cổng Thanh Toán</th>
                        <th style="width: 180px;">Mã Giao Dịch</th>
                        <th style="width: 140px;">Đơn Hàng / ND</th>
                        <th style="width: 130px;">Số Tiền Nhận</th>
                        <th style="width: 150px;">Trạng Thái Khớp</th>
                        <th>Chi Tiết & Sai Lệch</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($webhookLogs as $log)
                        <tr>
                            <td>
                                <div style="font-size: 13px; font-weight: 500; color: #1F1D1A;">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                                <div style="font-size: 11px; color: #8A8175;">IP: {{ $log->ip_address ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <span class="mayhen_admin_badge mayhen_admin_badge_info" style="font-size: 11px; text-transform: uppercase;">
                                    {{ $log->gateway }}
                                </span>
                            </td>
                            <td>
                                <div style="font-family: monospace; font-size: 13px; font-weight: 600; color: #2B6CB0;">
                                    {{ $log->transaction_id ?? 'N/A' }}
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 13px; font-weight: 500; color: #1F1D1A;">
                                    {{ $log->order_code ?? 'Không khớp mã' }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #1F1D1A; font-size: 13px;">
                                    {{ $log->formatted_amount }}
                                </div>
                            </td>
                            <td>
                                <span class="mayhen_admin_badge mayhen_admin_badge_{{ $log->status_badge_color }}">
                                    {{ $log->status_label }}
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 12px; color: {{ $log->error_reason ? '#C53030' : '#2D6A4F' }};">
                                    {{ $log->error_reason ?: 'Xác thực chữ ký hợp lệ & khớp lệnh tự động' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 48px; color: #8A8175;">
                                <p style="margin: 0; font-size: 14px;">Chưa có lượt gọi webhook thanh toán nào được ghi nhận.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($webhookLogs->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #EFE8DD;">
                {{ $webhookLogs->links() }}
            </div>
        @endif
    </div>
@endif

{{-- TAB 3: SỔ QUẢN LÝ HOÀN TIỀN --}}
@if($currentTab === 'refunds')
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th style="width: 140px;">Mã Đơn Hàng</th>
                        <th style="width: 140px;">Số Tiền Hoàn</th>
                        <th style="width: 220px;">Tài Khoản Thụ Hưởng</th>
                        <th>Lý Do Hoàn Tiền</th>
                        <th style="width: 140px;">Người Yêu Cầu</th>
                        <th style="width: 140px;">Trạng Thái</th>
                        <th style="width: 150px; text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($refunds as $ref)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #1F1D1A; font-size: 13px;">
                                    {{ $ref->order?->order_code ?? 'N/A' }}
                                </div>
                                <div style="font-size: 11px; color: #8A8175;">{{ $ref->created_at->format('d/m/Y') }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #C53030; font-size: 14px;">
                                    {{ $ref->formatted_amount }}
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 13px; font-weight: 500; color: #1F1D1A;">{{ $ref->bank_account_holder }}</div>
                                <div style="font-size: 11px; color: #8A8175;">{{ $ref->bank_name }} - {{ $ref->bank_account_number }}</div>
                            </td>
                            <td>
                                <div style="font-size: 13px; color: #4A453E;">{{ $ref->reconciliation_note }}</div>
                                @if($ref->review_note)
                                    <div style="font-size: 11px; color: #2D6A4F; margin-top: 2px;">Duyệt: {{ $ref->review_note }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 12px; color: #1F1D1A;">{{ $ref->creator?->name ?? 'Hệ thống' }}</div>
                            </td>
                            <td>
                                <span class="mayhen_admin_badge mayhen_admin_badge_{{ $ref->status_badge_color }}">
                                    {{ $ref->status_label }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                @if($ref->isPending())
                                    <form action="{{ route('admin.reconciliations.confirm_refund', $ref->id) }}" method="POST" onsubmit="return confirm('Xác nhận đã thực hiện chuyển tiền hoàn trả cho khách hàng?');">
                                        @csrf
                                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="padding: 4px 10px; font-size: 12px;">
                                            Xác Nhận Đã Chi
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size: 11px; color: #2D6A4F; font-weight: 500;">Hoàn tất chi trả</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 48px; color: #8A8175;">
                                <p style="margin: 0; font-size: 14px;">Không có yêu cầu hoàn tiền nào cần xử lý.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($refunds->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #EFE8DD;">
                {{ $refunds->links() }}
            </div>
        @endif
    </div>
@endif

{{-- MODAL 1: LẬP PHIẾU ĐỐI SOÁT MỚI --}}
<div id="createReconciliationModal" class="mayhen_admin_modal" style="display: none; position: fixed; inset: 0; background: rgba(31, 29, 26, 0.45); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="mayhen_admin_modal_dialog" style="background: #ffffff; border-radius: 12px; max-width: 580px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
        <form action="{{ route('admin.reconciliations.store') }}" method="POST">
            @csrf
            <div style="padding: 20px 24px; border-bottom: 1px solid #EFE8DD; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 18px; color: #1F1D1A; font-weight: 600;">Lập Phiếu Đối Soát Tài Chính</h3>
                <button type="button" onclick="closeModal('createReconciliationModal')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #8A8175;">&times;</button>
            </div>

            <div style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                        Chọn Đơn Hàng Cần Khớp Lệnh <span style="color: #e53e3e;">*</span>
                    </label>
                    <select name="order_id" class="mayhen_admin_form_select" required onchange="fillOrderAmount(this)">
                        <option value="">-- Chọn đơn hàng đang chờ thanh toán --</option>
                        @foreach($pendingOrders as $po)
                            <option value="{{ $po->id }}" data-amount="{{ $po->total_amount }}">
                                #{{ $po->order_code }} - {{ $po->user?->name }} ({{ number_format($po->total_amount) }} đ)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                            Loại Nghiệp Vụ <span style="color: #e53e3e;">*</span>
                        </label>
                        <select name="action_type" class="mayhen_admin_form_select" required>
                            <option value="manual_approval">Khớp lệnh thủ công (PAY-01)</option>
                            <option value="amount_mismatch_resolution">Xử lý lệch tiền (PAY-03)</option>
                        </select>
                    </div>

                    <div>
                        <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                            Số Tiền Thực Nhận (VNĐ) <span style="color: #e53e3e;">*</span>
                        </label>
                        <input type="number" name="amount" id="reconcileAmountInput" required min="1000" class="mayhen_admin_form_input">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                            Mã Giao Dịch Ngân Hàng <span style="color: #e53e3e;">*</span>
                        </label>
                        <input type="text" name="reference_code" required placeholder="FT24... hoặc Mã SePay" class="mayhen_admin_form_input" style="font-family: monospace;">
                    </div>

                    <div>
                        <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                            Ngân Hàng Chuyển Khoản
                        </label>
                        <input type="text" name="bank_name" placeholder="Vietcombank, MB, Techcombank..." class="mayhen_admin_form_input">
                    </div>
                </div>

                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                        Chủ Tài Khoản Chuyển Tiền
                    </label>
                    <input type="text" name="bank_account_holder" placeholder="Tên khách hàng trên sao kê ngân hàng..." class="mayhen_admin_form_input">
                </div>

                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                        Ghi Chú Kế Toán Đối Soát <span style="color: #e53e3e;">*</span>
                    </label>
                    <textarea name="reconciliation_note" required rows="2" placeholder="Ghi chú chi tiết lý do khớp lệnh (ví dụ: Khách chuyển qua mã QR nhưng không ghi đúng cú pháp)..." class="mayhen_admin_form_textarea"></textarea>
                </div>
            </div>

            <div style="padding: 16px 24px; border-top: 1px solid #EFE8DD; display: flex; justify-content: flex-end; gap: 12px; background: #FAF8F5; border-radius: 0 0 12px 12px;">
                <button type="button" onclick="closeModal('createReconciliationModal')" class="mayhen_admin_btn mayhen_admin_btn_outline">Hủy Bỏ</button>
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">Tạo Phiếu Đối Soát</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: TỪ CHỐI ĐỐI SOÁT --}}
<div id="rejectModal" class="mayhen_admin_modal" style="display: none; position: fixed; inset: 0; background: rgba(31, 29, 26, 0.45); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="mayhen_admin_modal_dialog" style="background: #ffffff; border-radius: 12px; max-width: 450px; width: 90%; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
        <form id="rejectForm" method="POST">
            @csrf
            <div style="padding: 20px 24px; border-bottom: 1px solid #EFE8DD;">
                <h3 style="margin: 0; font-size: 16px; color: #C53030; font-weight: 600;">Từ Chối Phiếu Đối Soát</h3>
            </div>
            <div style="padding: 20px 24px;">
                <label class="mayhen_admin_form_label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                    Lý Do Từ Chối <span style="color: #e53e3e;">*</span>
                </label>
                <textarea name="reason" required rows="3" placeholder="Nhập lý do không thể duyệt phiếu đối soát này..." class="mayhen_admin_form_textarea"></textarea>
            </div>
            <div style="padding: 16px 24px; border-top: 1px solid #EFE8DD; display: flex; justify-content: flex-end; gap: 12px; background: #FAF8F5; border-radius: 0 0 12px 12px;">
                <button type="button" onclick="closeModal('rejectModal')" class="mayhen_admin_btn mayhen_admin_btn_outline">Hủy</button>
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="background: #C53030;">Xác Nhận Từ Chối</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.style.display = 'flex';
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.style.display = 'none';
}

function openRejectModal(id) {
    const form = document.getElementById('rejectForm');
    if (form) {
        form.action = `/admin/reconciliations/${id}/reject`;
        openModal('rejectModal');
    }
}

function fillOrderAmount(select) {
    const selectedOption = select.options[select.selectedIndex];
    const amount = selectedOption.getAttribute('data-amount');
    const input = document.getElementById('reconcileAmountInput');
    if (amount && input) {
        input.value = amount;
    }
}

window.addEventListener('click', function(e) {
    ['createReconciliationModal', 'rejectModal'].forEach(id => {
        const modal = document.getElementById(id);
        if (modal && e.target === modal) modal.style.display = 'none';
    });
});
</script>
@endsection

