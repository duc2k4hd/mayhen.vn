@extends('admin.layouts.master')

@section('title', 'Quản Trị Sổ Hoàn Tiền & Xử Lý Khiếu Nại')

@section('content')
<div class="mayhen_admin_refunds_cockpit">
    <!-- Header Section -->
    <div class="mayhen_admin_page_header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="mayhen_admin_badge mayhen_admin_badge--gold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Finance & Refund Operations</span>
                <span style="color: var(--mh-admin-muted); font-size: 0.85rem;">Mục 140 PRD V3 & Mục 9.3 DOCX</span>
            </div>
            <h1 style="font-size: 1.6rem; font-weight: 600; color: var(--mh-admin-charcoal); margin: 0;">
                Quản Trị Sổ Hoàn Tiền & Xử Lý Khiếu Nại Giao Dịch
            </h1>
            <p style="margin: 0.25rem 0 0 0; color: var(--mh-admin-muted); font-size: 0.9rem;">
                Thẩm định yêu cầu hoàn tiền 2 bước (Two-man rule), đối soát lệnh chuyển khoản hoàn và tự động hạ cấp quyền thiệp cưới an toàn.
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('admin.refunds.export', request()->query()) }}" class="mayhen_admin_btn mayhen_admin_btn--outline" style="font-size: 0.85rem;">
                📥 Xuất File CSV
            </a>
            <a href="{{ route('admin.refunds.index', ['tab' => 'fast_refund']) }}" class="mayhen_admin_btn mayhen_admin_btn--primary" style="font-size: 0.85rem;">
                + Tạo Yêu Cầu Hoàn Tiền
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="mayhen_admin_kpi_grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid #85182A;">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--mh-admin-muted); text-transform: uppercase;">Tổng Tiền Đã Hoàn</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #85182A; margin: 0.4rem 0 0.25rem 0;">
                {{ number_format($kpis['total_refunded_amount'], 0, ',', '.') }} ₫
            </div>
            <div style="font-size: 0.8rem; color: var(--mh-admin-muted);">Chi trả thực tế từ tài khoản ngân hàng</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid #059669;">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--mh-admin-muted); text-transform: uppercase;">Đơn Hoàn Tất</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #059669; margin: 0.4rem 0 0.25rem 0;">
                {{ number_format($kpis['completed_count']) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--mh-admin-muted);">Đơn hàng đã được giải quyết minh bạch</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid #D97706;">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--mh-admin-muted); text-transform: uppercase;">Chờ Thẩm Định</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #D97706; margin: 0.4rem 0 0.25rem 0;">
                {{ number_format($kpis['pending_count']) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--mh-admin-muted);">Yêu cầu cần quản lý phê duyệt (P1)</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid #7C3AED;">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--mh-admin-muted); text-transform: uppercase;">Tỷ Lệ Hoàn Tiền</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #7C3AED; margin: 0.4rem 0 0.25rem 0;">
                {{ number_format($kpis['refund_rate'], 2) }}%
            </div>
            <div style="font-size: 0.8rem; color: var(--mh-admin-muted);">Tỷ trọng so với tổng doanh thu nền tảng</div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div style="display: flex; gap: 0.5rem; border-bottom: 1px solid var(--mh-admin-border); margin-bottom: 1.5rem;">
        <a href="{{ route('admin.refunds.index', ['tab' => 'requests']) }}" 
           class="mayhen_admin_tab {{ $currentTab === 'requests' ? 'mayhen_admin_tab--active' : '' }}"
           style="padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'requests' ? 'var(--mh-admin-gold)' : 'transparent' }}; color: {{ $currentTab === 'requests' ? 'var(--mh-admin-gold)' : 'var(--mh-admin-muted)' }};">
            Sổ Yêu Cầu Hoàn Tiền ({{ $requests->total() }})
        </a>
        <a href="{{ route('admin.refunds.index', ['tab' => 'fast_refund']) }}" 
           class="mayhen_admin_tab {{ $currentTab === 'fast_refund' ? 'mayhen_admin_tab--active' : '' }}"
           style="padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'fast_refund' ? 'var(--mh-admin-gold)' : 'transparent' }}; color: {{ $currentTab === 'fast_refund' ? 'var(--mh-admin-gold)' : 'var(--mh-admin-muted)' }};">
            Tạo Yêu Cầu Hoàn Tiền
        </a>
        <a href="{{ route('admin.refunds.index', ['tab' => 'policy']) }}" 
           class="mayhen_admin_tab {{ $currentTab === 'policy' ? 'mayhen_admin_tab--active' : '' }}"
           style="padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'policy' ? 'var(--mh-admin-gold)' : 'transparent' }}; color: {{ $currentTab === 'policy' ? 'var(--mh-admin-gold)' : 'var(--mh-admin-muted)' }};">
            Quy Chuẩn Chính Sách Hoàn Tiền (SOP)
        </a>
    </div>

    @if($currentTab === 'requests')
    <!-- TAB 1: SỔ YÊU CẦU HOÀN TIỀN -->
    <div class="mayhen_admin_card" style="padding: 1.5rem;">
        <!-- Filters Bar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--mh-admin-border); padding-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
            <div style="font-weight: 600; color: var(--mh-admin-charcoal); font-size: 1.1rem;">
                Danh Sách Hồ Sơ Hoàn Tiền & Tranh Chấp
            </div>
            
            <form method="GET" action="{{ route('admin.refunds.index') }}" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="tab" value="requests">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo mã đơn, khách hàng, mã CK..." class="mayhen_admin_input" style="padding: 0.4rem 0.75rem; font-size: 0.85rem; width: 260px;">
                <select name="status" class="mayhen_admin_input" style="padding: 0.4rem 0.75rem; font-size: 0.85rem;" onchange="this.form.submit()">
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>-- Tất cả trạng thái --</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ Thẩm Định (Pending)</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Đã Hoàn Tiền (Completed)</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Đã Từ Chối (Rejected)</option>
                </select>
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="font-size: 0.85rem; padding: 0.4rem 0.75rem;">
                    Lọc Dữ Liệu
                </button>
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--mh-admin-border); text-align: left; color: var(--mh-admin-muted); font-size: 0.8rem; text-transform: uppercase;">
                        <th style="padding: 0.75rem 1rem;">Mã Yêu Cầu</th>
                        <th style="padding: 0.75rem 1rem;">Đơn Hàng / Khách Hàng</th>
                        <th style="padding: 0.75rem 1rem;">Số Tiền Hoàn</th>
                        <th style="padding: 0.75rem 1rem;">Lý Do Khiếu Nại</th>
                        <th style="padding: 0.75rem 1rem;">Người Đề Xuất / Duyệt</th>
                        <th style="padding: 0.75rem 1rem;">Trạng Thái</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr style="border-bottom: 1px solid var(--mh-admin-border); vertical-align: middle;">
                        <td style="padding: 0.75rem 1rem; font-family: monospace; font-size: 0.85rem; color: #334155;">
                            <strong>{{ $req->reference_code }}</strong>
                            <div style="font-size: 0.75rem; color: var(--mh-admin-muted); font-family: inherit;">
                                {{ $req->created_at->format('d/m/Y H:i') }}
                            </div>
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            @if($req->order)
                                <a href="{{ route('admin.orders.show', $req->order->id) }}" style="color: var(--mh-admin-gold); text-decoration: none; font-weight: 600;">
                                    #{{ $req->order->order_code }}
                                </a>
                                <div style="font-size: 0.8rem; color: var(--mh-admin-charcoal);">
                                    {{ $req->order->user->name ?? 'Khách hàng ẩn danh' }}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">
                                    {{ $req->order->plan->name ?? 'Gói thiệp cưới' }}
                                </div>
                            @else
                                <span style="color: var(--mh-admin-muted);">Đơn #{{ $req->order_id }}</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem; font-family: monospace; font-weight: 700; color: #85182A; font-size: 0.95rem;">
                            {{ number_format($req->amount, 0, ',', '.') }} ₫
                        </td>
                        <td style="padding: 0.75rem 1rem; max-width: 250px;">
                            <div style="font-size: 0.85rem; color: var(--mh-admin-charcoal); line-height: 1.4;">
                                {{ $req->reconciliation_note ?? 'Không có ghi chú' }}
                            </div>
                            @if($req->bank_account_number)
                                <div style="font-size: 0.75rem; color: #2563EB; margin-top: 0.2rem;">
                                    🏦 {{ $req->bank_name }}: <code>{{ $req->bank_account_number }}</code> ({{ $req->bank_account_holder }})
                                </div>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.8rem;">
                            <div>Đề xuất: <strong>{{ $req->creator->name ?? 'Hệ thống' }}</strong></div>
                            @if($req->approver)
                                <div style="color: var(--mh-admin-muted); margin-top: 0.2rem;">
                                    Thẩm định: <strong>{{ $req->approver->name }}</strong>
                                </div>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            @if($req->status === 'completed' || $req->status === 'approved')
                                <span class="mayhen_admin_badge" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0;">
                                    ● Đã Hoàn Tiền
                                </span>
                            @elseif($req->status === 'pending')
                                <span class="mayhen_admin_badge" style="background: #FFFBEB; color: #D97706; border: 1px solid #FDE68A;">
                                    ⏳ Chờ Thẩm Định
                                </span>
                            @elseif($req->status === 'rejected')
                                <span class="mayhen_admin_badge" style="background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA;">
                                    ✕ Đã Từ Chối
                                </span>
                            @else
                                <span class="mayhen_admin_badge">{{ $req->status }}</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right;">
                            @if($req->isPending())
                                <div style="display: inline-flex; gap: 0.4rem; align-items: center;">
                                    <button type="button" 
                                            class="mayhen_admin_btn mayhen_admin_btn--primary mayhen_admin_btn--sm" 
                                            style="font-size: 0.75rem; padding: 0.35rem 0.65rem;"
                                            onclick="openApproveModal({{ json_encode($req) }})">
                                        Duyệt Hoàn Tiền
                                    </button>
                                    <button type="button" 
                                            class="mayhen_admin_btn mayhen_admin_btn--outline mayhen_admin_btn--sm" 
                                            style="font-size: 0.75rem; padding: 0.35rem 0.65rem;"
                                            onclick="openRejectModal({{ json_encode($req) }})">
                                        Từ Chối
                                    </button>
                                </div>
                            @else
                                <span style="font-size: 0.8rem; color: var(--mh-admin-muted);">
                                    {{ $req->reconciled_at ? $req->reconciled_at->format('d/m/Y') : 'Đã kết thúc' }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 3rem; text-align: center; color: var(--mh-admin-muted);">
                            Chưa có yêu cầu hoàn tiền nào trong danh sách.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
        <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
            {{ $requests->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
    @endif

    @if($currentTab === 'fast_refund')
    <!-- TAB 2: TẠO NHANH YÊU CẦU HOÀN TIỀN -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
        <!-- Left: Form tạo yêu cầu -->
        <div class="mayhen_admin_card" style="padding: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                <span class="mayhen_admin_badge mayhen_admin_badge--gold">Step 1: CSKH Đề Xuất</span>
                <h2 style="font-size: 1.15rem; font-weight: 600; color: var(--mh-admin-charcoal); margin: 0;">
                    Phiếu Đề Xuất Hoàn Tiền Đơn Hàng
                </h2>
            </div>
            <p style="color: var(--mh-admin-muted); font-size: 0.85rem; line-height: 1.5; margin-bottom: 1.25rem;">
                Nhập mã đơn hàng của cặp đôi và thông tin chuyển khoản ngân hàng. Sau khi gửi, hồ sơ sẽ chuyển sang bước 2 để Quản lý cấp cao thẩm định chi tiền.
            </p>

            <form method="POST" action="{{ route('admin.refunds.store') }}" style="display: flex; flex-direction: column; gap: 1rem;">
                @csrf
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--mh-admin-charcoal); margin-bottom: 0.35rem;">
                        Mã Đơn Hàng (Order Code)
                    </label>
                    <input type="text" name="order_code" placeholder="VD: MH20261111..." class="mayhen_admin_input" style="width: 100%;" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--mh-admin-charcoal); margin-bottom: 0.35rem;">
                            Số Tiền Hoàn (VNĐ)
                        </label>
                        <input type="number" name="amount" placeholder="Để trống nếu hoàn 100%" class="mayhen_admin_input" style="width: 100%;">
                        <small style="color: var(--mh-admin-muted); font-size: 0.75rem;">Mặc định lấy toàn bộ giá trị đơn hàng.</small>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--mh-admin-charcoal); margin-bottom: 0.35rem;">
                            Điều Khoản Áp Dụng
                        </label>
                        <select name="policy_clause" class="mayhen_admin_input" style="width: 100%;" required>
                            <option value="24h_pre_publish">Hoàn 100% trong 24h & Chưa Xuất Bản</option>
                            <option value="system_incident">Sự Cố Kỹ Thuật Hệ Thống</option>
                            <option value="force_majeure">Bất Khả Kháng / Hủy Tiệc Cưới</option>
                            <option value="other">Trường Hợp Ngoại Lệ Khác</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--mh-admin-charcoal); margin-bottom: 0.35rem;">
                        Thông Tin Ngân Hàng Khách Hàng Nhận Tiền
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem;">
                        <input type="text" name="bank_name" placeholder="Ngân hàng (MB, VCB...)" class="mayhen_admin_input">
                        <input type="text" name="bank_account_number" placeholder="Số tài khoản" class="mayhen_admin_input">
                        <input type="text" name="bank_account_holder" placeholder="Tên chủ tài khoản" class="mayhen_admin_input">
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--mh-admin-charcoal); margin-bottom: 0.35rem;">
                        Lý Do Khiếu Nại & Ghi Chú CSKH
                    </label>
                    <textarea name="reason" rows="3" placeholder="Ghi rõ phản ánh của dâu rể, thời điểm thanh toán và bằng chứng đối chiếu..." class="mayhen_admin_input" style="width: 100%;" required></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 0.5rem;">
                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary">
                        Tạo Yêu Cầu Hoàn Tiền Chờ Thẩm Định
                    </button>
                </div>
            </form>
        </div>

        <!-- Right: Danh sách đơn hàng gần đây -->
        <div class="mayhen_admin_card" style="padding: 1.5rem;">
            <div style="font-weight: 600; color: var(--mh-admin-charcoal); font-size: 1.1rem; margin-bottom: 0.75rem;">
                Đơn Hàng Gần Đây Có Thể Hoàn Tiền
            </div>
            <p style="color: var(--mh-admin-muted); font-size: 0.825rem; margin-bottom: 1rem;">
                Bấm vào mã đơn hàng để tự động điền vào biểu mẫu bên trái.
            </p>

            <div style="overflow-y: auto; max-height: 480px; display: flex; flex-direction: column; gap: 0.5rem;">
                @forelse($eligibleOrders as $ord)
                <div style="padding: 0.75rem 1rem; border: 1px solid var(--mh-admin-border); border-radius: 6px; display: flex; justify-content: space-between; align-items: center; background: #FAF9F6;">
                    <div>
                        <div style="font-weight: 600; color: var(--mh-admin-charcoal);">
                            #{{ $ord->order_code }} &bull; {{ $ord->user->name ?? 'N/A' }}
                        </div>
                        <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">
                            Gói: {{ $ord->plan->name ?? 'Gói cước' }} &bull; Thanh toán: {{ $ord->paid_at ? $ord->paid_at->format('d/m/Y H:i') : 'Chưa ghi nhận' }}
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-family: monospace; font-weight: 700; color: #85182A; display: block;">
                            {{ number_format($ord->amount, 0, ',', '.') }} ₫
                        </span>
                        <button type="button" 
                                class="mayhen_admin_btn mayhen_admin_btn--outline mayhen_admin_btn--sm"
                                style="font-size: 0.75rem; padding: 0.2rem 0.5rem; margin-top: 0.25rem;"
                                onclick="document.getElementsByName('order_code')[0].value = '{{ $ord->order_code }}'">
                            Chọn Đơn Này
                        </button>
                    </div>
                </div>
                @empty
                <div style="color: var(--mh-admin-muted); font-size: 0.85rem; text-align: center; padding: 2rem;">
                    Không có đơn hàng nào trong danh sách gần đây.
                </div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    @if($currentTab === 'policy')
    <!-- TAB 3: QUY CHUẨN CHÍNH SÁCH HOÀN TIỀN 100% -->
    <div class="mayhen_admin_card" style="padding: 2rem; background: #FAF9F6; border: 1px solid var(--mh-admin-border);">
        <div style="max-width: 800px; margin: 0 auto;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--mh-admin-border); padding-bottom: 1rem;">
                <span style="font-size: 2rem;">📜</span>
                <div>
                    <h2 style="font-size: 1.35rem; font-weight: 600; color: var(--mh-admin-charcoal); margin: 0;">
                        Chính Sách Hoàn Tiền Chuẩn Mây Hẹn Atelier (Mục 9.3 DOCX)
                    </h2>
                    <p style="margin: 0.2rem 0 0 0; color: var(--mh-admin-muted); font-size: 0.85rem;">
                        Cam kết minh bạch tài chính và bảo đảm quyền lợi tối cao cho các cặp đôi.
                    </p>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1.25rem; font-size: 0.9rem; line-height: 1.6; color: #334155;">
                <div style="padding: 1rem; background: #fff; border-radius: 8px; border: 1px solid #E2E8F0;">
                    <strong style="color: #0F172A; font-size: 1rem; display: block; margin-bottom: 0.35rem;">
                        1. Điều Kiện Hoàn Tiền 100% Không Điều Kiện (24 Hours Money-back Guarantee)
                    </strong>
                    Mọi đơn hàng mua gói dịch vụ thiệp cưới đều được quyền yêu cầu hoàn tiền 100% trong vòng <strong>24 giờ</strong> kể từ thời điểm thanh toán thành công, với điều kiện thiệp cưới chưa xuất bản chính thức (ở trạng thái <code>draft</code>).
                </div>

                <div style="padding: 1rem; background: #fff; border-radius: 8px; border: 1px solid #E2E8F0;">
                    <strong style="color: #0F172A; font-size: 1rem; display: block; margin-bottom: 0.35rem;">
                        2. Hoàn Tiền Do Sự Cố Kỹ Thuật Hệ Thống (Technical Incident Guarantee)
                    </strong>
                    Trường hợp hệ thống Mây Hẹn phát sinh sự cố gián đoạn dịch vụ nghiêm trọng, lỗi không tải được ảnh cưới hoặc lỗi chức năng cốt lõi không thể khắc phục trong vòng 12 giờ trước lễ cưới, cặp đôi sẽ được hoàn tiền 100% kèm văn bản xin lỗi từ ban quản trị.
                </div>

                <div style="padding: 1rem; background: #fff; border-radius: 8px; border: 1px solid #E2E8F0;">
                    <strong style="color: #0F172A; font-size: 1rem; display: block; margin-bottom: 0.35rem;">
                        3. Nguyên Tắc Kiểm Soát Kép (Two-man Rule)
                    </strong>
                    Mọi quyết định hoàn tiền bắt buộc trải qua 2 bước độc lập: Nhân viên CSKH/Vận hành lập phiếu đề xuất &rarr; Quản lý tài chính/Super Admin thẩm định tài khoản thụ hưởng và xác nhận chuyển khoản. Người đề xuất không được tự ý duyệt hoàn tiền cho chính hồ sơ mình tạo.
                </div>

                <div style="padding: 1rem; background: #fff; border-radius: 8px; border: 1px solid #E2E8F0;">
                    <strong style="color: #0F172A; font-size: 1rem; display: block; margin-bottom: 0.35rem;">
                        4. Hạ Cấp Dịch Vụ & Thu Hồi Hoa Hồng Tự Động (Automatic Downgrade & Revert)
                    </strong>
                    Sau khi lệnh hoàn tiền được phê duyệt thành công, hệ thống tự động:
                    <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem;">
                        <li>Hạ cấp thiệp cưới về trạng thái Nháp (Draft) hoặc Gói Miễn Phí.</li>
                        <li>Ngắt kết nối tên miền riêng độc bản (nếu có).</li>
                        <li>Thu hồi toàn bộ hoa hồng của đối tác/studio (nếu đơn hàng có mã giới thiệu).</li>
                        <li>Ghi nhận toàn bộ vết lịch sử vào sổ kiểm toán <code>AuditLogger</code>.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal Thẩm Định & Duyệt Hoàn Tiền (Two-man Approval Modal) -->
<div id="mayhen_approve_refund_modal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.45); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="mayhen_admin_card" style="width: 100%; max-width: 520px; padding: 1.5rem; background: #fff; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); margin: 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--mh-admin-border); padding-bottom: 0.75rem;">
            <h3 style="font-size: 1.15rem; font-weight: 600; color: var(--mh-admin-charcoal); margin: 0;">
                Thẩm Định & Duyệt Chi Hoàn Tiền (Step 2)
            </h3>
            <button type="button" onclick="closeApproveModal()" style="border: none; background: transparent; font-size: 1.25rem; cursor: pointer; color: var(--mh-admin-muted);">&times;</button>
        </div>

        <form id="mayhen_approve_refund_form" method="POST" action="">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="padding: 0.75rem 1rem; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 6px; font-size: 0.85rem;">
                    <div>Đơn hàng: <strong id="modal_order_code"></strong></div>
                    <div>Số tiền hoàn: <strong id="modal_refund_amount" style="color: #85182A;"></strong></div>
                    <div id="modal_bank_info" style="color: #2563EB; margin-top: 0.25rem;"></div>
                </div>

                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--mh-admin-charcoal); margin-bottom: 0.35rem;">
                        Mã Giao Dịch Chuyển Khoản Ngân Hàng (Payout Reference Code)
                    </label>
                    <input type="text" name="payout_reference" placeholder="VD: FT26... hoặc Mã giao dịch SePay/Vietinbank" class="mayhen_admin_input" style="width: 100%;" required>
                    <small style="color: var(--mh-admin-muted); font-size: 0.75rem;">Mã ủy nhiệm chi hoặc số tham chiếu ngân hàng thực tế.</small>
                </div>

                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--mh-admin-charcoal); margin-bottom: 0.35rem;">
                        Ghi Chú Phê Duyệt / Bằng Chứng Đối Soát
                    </label>
                    <textarea name="review_note" rows="2" placeholder="Ghi chú đối soát của kế toán trưởng..." class="mayhen_admin_input" style="width: 100%;"></textarea>
                </div>

                <div style="margin-top: 0.5rem; display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--mh-admin-border); padding-top: 1rem;">
                    <button type="button" class="mayhen_admin_btn mayhen_admin_btn--outline" onclick="closeApproveModal()">
                        Hủy Bỏ
                    </button>
                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="background: #059669; border-color: #059669;">
                        Xác Nhận Đã Chuyển Khoản & Duyệt Hoàn Tiền
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Từ Chối Hoàn Tiền -->
<div id="mayhen_reject_refund_modal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.45); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="mayhen_admin_card" style="width: 100%; max-width: 480px; padding: 1.5rem; background: #fff; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); margin: 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--mh-admin-border); padding-bottom: 0.75rem;">
            <h3 style="font-size: 1.15rem; font-weight: 600; color: #DC2626; margin: 0;">
                Từ Chối Yêu Cầu Hoàn Tiền
            </h3>
            <button type="button" onclick="closeRejectModal()" style="border: none; background: transparent; font-size: 1.25rem; cursor: pointer; color: var(--mh-admin-muted);">&times;</button>
        </div>

        <form id="mayhen_reject_refund_form" method="POST" action="">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <p style="font-size: 0.85rem; color: var(--mh-admin-muted); margin: 0;">
                    Vui lòng ghi rõ lý do từ chối yêu cầu hoàn tiền để CSKH phản hồi giải thích thỏa đáng cho cặp đôi.
                </p>

                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--mh-admin-charcoal); margin-bottom: 0.35rem;">
                        Lý Do Từ Chối (Minh Bạch)
                    </label>
                    <textarea name="reject_reason" rows="3" placeholder="VD: Đơn hàng đã quá hạn 24 giờ và thiệp cưới đã xuất bản gửi đến 150 khách mời..." class="mayhen_admin_input" style="width: 100%;" required></textarea>
                </div>

                <div style="margin-top: 0.5rem; display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--mh-admin-border); padding-top: 1rem;">
                    <button type="button" class="mayhen_admin_btn mayhen_admin_btn--outline" onclick="closeRejectModal()">
                        Hủy Bỏ
                    </button>
                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--danger" style="background: #DC2626; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer;">
                        Xác Nhận Từ Chối
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openApproveModal(req) {
    const modal = document.getElementById('mayhen_approve_refund_modal');
    const form = document.getElementById('mayhen_approve_refund_form');
    
    form.action = `/admin/orders/refunds/${req.id}/approve`;
    document.getElementById('modal_order_code').innerText = req.order ? req.order.order_code : '#' + req.order_id;
    document.getElementById('modal_refund_amount').innerText = new Intl.NumberFormat('vi-VN').format(req.amount) + ' ₫';
    
    const bankInfo = req.bank_account_number ? `🏦 ${req.bank_name || ''}: ${req.bank_account_number} (${req.bank_account_holder || ''})` : 'Chưa có STK ngân hàng ghi chú';
    document.getElementById('modal_bank_info').innerText = bankInfo;

    modal.style.display = 'flex';
}

function closeApproveModal() {
    document.getElementById('mayhen_approve_refund_modal').style.display = 'none';
}

function openRejectModal(req) {
    const modal = document.getElementById('mayhen_reject_refund_modal');
    const form = document.getElementById('mayhen_reject_refund_form');
    
    form.action = `/admin/orders/refunds/${req.id}/reject`;
    modal.style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('mayhen_reject_refund_modal').style.display = 'none';
}
</script>
@endsection

