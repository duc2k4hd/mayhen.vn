@extends('admin.layouts.master')

@section('title', 'Sổ Quyết Toán Hoa Hồng Đối Tác — Mây Hẹn Admin')

@section('content')
<div class="mayhen_admin_page">
    {{-- Header --}}
    <div class="mayhen_admin_page_header">
        <div>
            <div class="mayhen_admin_breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Tổng quan</a>
                <span>/</span>
                <a href="{{ route('admin.partners.index') }}">Đối tác</a>
                <span>/</span>
                <span>Quyết toán hoa hồng</span>
            </div>
            <h1 class="mayhen_admin_page_title">Sổ Quyết Toán Hoa Hồng B2B</h1>
            <p class="mayhen_admin_page_subtitle">Giám sát, đối soát và thực hiện chi trả hoa hồng cho mạng lưới Studio & Wedding Planner với chứng từ ngân hàng bắt buộc (Chuẩn P0).</p>
        </div>
        <div class="mayhen_admin_page_actions">
            <a href="{{ route('admin.partners.commissions.export', request()->query()) }}" class="mayhen_admin_btn mayhen_admin_btn--outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                Xuất Báo Cáo Đối Soát (CSV)
            </a>
            <a href="{{ route('admin.partners.index') }}" class="mayhen_admin_btn mayhen_admin_btn--ghost">
                &larr; Danh sách đối tác
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="mayhen_admin_alert mayhen_admin_alert--success">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mayhen_admin_alert mayhen_admin_alert--danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="mayhen_admin_card mayhen_admin_mb_4">
        <form method="GET" action="{{ route('admin.partners.commissions') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 220px;">
                <select name="partner_id" class="mayhen_admin_select" style="width: 100%;">
                    <option value="">Tất cả đối tác Studio</option>
                    @foreach($partners as $p)
                        <option value="{{ $p->id }}" {{ (string)$filters['partner_id'] === (string)$p->id ? 'selected' : '' }}>
                            {{ $p->business_name }} ({{ $p->partner_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="width: 170px;">
                <select name="status" class="mayhen_admin_select" style="width: 100%;">
                    <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                    <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="approved" {{ $filters['status'] === 'approved' ? 'selected' : '' }}>Đã duyệt (Chờ chi)</option>
                    <option value="paid" {{ $filters['status'] === 'paid' ? 'selected' : '' }}>Đã chi trả hoàn tất</option>
                </select>
            </div>

            <div style="width: 160px;">
                <select name="type" class="mayhen_admin_select" style="width: 100%;">
                    <option value="all" {{ $filters['type'] === 'all' ? 'selected' : '' }}>Tất cả loại</option>
                    <option value="commission" {{ $filters['type'] === 'commission' ? 'selected' : '' }}>Hoa hồng phát sinh</option>
                    <option value="payout" {{ $filters['type'] === 'payout' ? 'selected' : '' }}>Yêu cầu rút tiền / Chi trả</option>
                </select>
            </div>

            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary">Lọc</button>
            @if(!empty($filters['partner_id']) || $filters['status'] !== 'all' || $filters['type'] !== 'all')
                <a href="{{ route('admin.partners.commissions') }}" class="mayhen_admin_btn mayhen_admin_btn--ghost">Xóa lọc</a>
            @endif
        </form>
    </div>

    {{-- Commissions Table --}}
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_table_wrapper">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th>Mã GD</th>
                        <th>Đối Tác Studio</th>
                        <th>Loại & Mô Tả</th>
                        <th>Số Tiền</th>
                        <th>Mã Ngân Hàng / Đối Soát</th>
                        <th>Trạng Thái</th>
                        <th>Thời Gian</th>
                        <th style="text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $comm)
                        <tr>
                            <td style="font-family: monospace; font-weight: 700; color: #1F1D1A;">
                                #{{ $comm->id }}
                            </td>
                            <td>
                                @if($comm->partner)
                                    <div style="font-weight: 600; color: #1F1D1A;">{{ $comm->partner->business_name }}</div>
                                    <div style="font-size: 11px; font-family: monospace; color: #9C7A4A;">{{ $comm->partner->partner_code }}</div>
                                @else
                                    <span style="color: #8C827A;">Không xác định</span>
                                @endif
                            </td>
                            <td>
                                <div>
                                    @if($comm->type === 'payout')
                                        <span class="mayhen_admin_badge mayhen_admin_badge--danger">Chi trả / Rút tiền</span>
                                    @else
                                        <span class="mayhen_admin_badge mayhen_admin_badge--info">Hoa hồng đơn</span>
                                    @endif
                                </div>
                                <div style="font-size: 12px; color: #555; margin-top: 4px;">{{ $comm->description ?? 'Chiết khấu' }}</div>
                                @if($comm->order)
                                    <div style="font-size: 11px; color: #8C827A;">Mã đơn: {{ $comm->order->order_code }}</div>
                                @endif
                            </td>
                            <td>
                                <strong style="font-size: 15px; color: {{ $comm->type === 'payout' ? '#9B1C1C' : '#2E7D32' }};">
                                    {{ $comm->formatted_amount }}
                                </strong>
                            </td>
                            <td>
                                @if($comm->payout_reference_code)
                                    <span style="font-family: monospace; font-size: 12px; background: #FAF8F5; border: 1px solid #EAE3D9; padding: 3px 6px; border-radius: 4px; color: #1F1D1A;">
                                        {{ $comm->payout_reference_code }}
                                    </span>
                                    @if($comm->reconciled_at)
                                        <div style="font-size: 11px; color: #8C827A; margin-top: 2px;">
                                            {{ $comm->reconciled_at->format('d/m/Y H:i') }}
                                            @if($comm->reconciledBy)
                                                ({{ $comm->reconciledBy->name }})
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <span style="color: #8C827A; font-size: 12px; font-style: italic;">Chưa quyết toán</span>
                                @endif
                            </td>
                            <td>
                                @if($comm->status === 'paid')
                                    <span class="mayhen_admin_badge mayhen_admin_badge--success">Đã Chi Trả</span>
                                @elseif($comm->status === 'approved')
                                    <span class="mayhen_admin_badge mayhen_admin_badge--warning">Đã Duyệt (Chờ chi)</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge--ghost">Chờ Xử Lý</span>
                                @endif
                            </td>
                            <td style="font-size: 12px; color: #8C827A;">
                                {{ $comm->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td style="text-align: right;">
                                @if($comm->status !== 'paid')
                                    <button type="button" class="mayhen_admin_btn mayhen_admin_btn--sm mayhen_admin_btn--primary" onclick="openSettleModal({{ $comm->id }}, '{{ addslashes($comm->partner ? $comm->partner->business_name : 'Đối tác') }}', '{{ $comm->formatted_amount }}')">
                                        Quyết Toán
                                    </button>
                                @else
                                    <span style="font-size: 12px; color: #2E7D32; font-weight: 600;">✓ Hoàn tất</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 36px; color: #8C827A;">
                                Không tìm thấy bản ghi giao dịch hoa hồng nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($commissions->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #EAE3D9;">
                {{ $commissions->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal Quyết Toán Có Bằng Chứng Ngân Hàng (P0) --}}
<div id="settleModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #FFFFFF; border-radius: 16px; max-width: 480px; width: 90%; margin: 80px auto; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; color: #1F1D1A; margin-bottom: 8px;">
            Quyết Toán & Chi Trả Hoa Hồng
        </h3>
        <p style="font-size: 13px; color: #8C827A; margin-bottom: 16px;">
            Đối tác: <strong id="settlePartnerName" style="color: #1F1D1A;"></strong> • Số tiền: <strong id="settleAmount" style="color: #9C7A4A;"></strong>
        </p>

        <form id="settleForm" method="POST" action="">
            @csrf
            <div style="margin-bottom: 16px;">
                <label class="mayhen_admin_label" style="font-size: 12px;">Mã Giao Dịch Chuyển Khoản Ngân Hàng (Bắt buộc) <span style="color: #9B1C1C;">*</span></label>
                <input type="text" name="bank_reference_code" class="mayhen_admin_input" style="width: 100%; font-family: monospace;" placeholder="Ví dụ: FT26315891234, VCB123984..." required minlength="4">
                <span style="font-size: 11px; color: #8C827A; display: block; margin-top: 4px;">
                    Mã chuẩn từ thông báo giao dịch ngân hàng thực tế để phục vụ đối soát kế toán.
                </span>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="mayhen_admin_label" style="font-size: 12px;">Ghi chú đối soát</label>
                <textarea name="reconciliation_note" rows="3" class="mayhen_admin_input" style="width: 100%; resize: vertical;" placeholder="Chuyển khoản thanh toán hoa hồng qua ngân hàng Techcombank..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="mayhen_admin_btn mayhen_admin_btn--outline" onclick="closeSettleModal()">
                    Hủy Bỏ
                </button>
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary">
                    Xác Nhận Quyết Toán
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openSettleModal(id, partnerName, amount) {
        document.getElementById('settlePartnerName').textContent = partnerName;
        document.getElementById('settleAmount').textContent = amount;
        document.getElementById('settleForm').action = '/admin/partners/commissions/' + id + '/settle';
        document.getElementById('settleModal').style.display = 'block';
    }

    function closeSettleModal() {
        document.getElementById('settleModal').style.display = 'none';
    }
</script>
@endsection

