@extends('admin.layouts.master')

@section('title', 'Quản Trị Quyền Riêng Tư & Lưu Trữ Dữ Liệu (DSAR & Retention)')
@section('page_title', '🛡️ Quản Trị Quyền Riêng Tư & Lưu Trữ Dữ Liệu (NXT-10)')

@section('content')
    {{-- KPI Overview Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #D97706;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Tiệc Cưới Quá Hạn Lưu Trữ
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #D97706;">
                {{ number_format($kpis['eligible_retention_weddings'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #B45309; margin-top: 4px;">Đã qua cưới &gt; 180 ngày cần ẩn danh PII</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #E65100;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Yêu Cầu DSAR Chờ Duyệt
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #E65100;">
                {{ number_format($kpis['pending_requests'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #E65100; margin-top: 4px; font-weight: 600;">Cần Quản lý phê duyệt (Step 1)</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #2E7D32;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Đã Ẩn Danh Hóa An Toàn
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #2E7D32;">
                {{ number_format($kpis['executed_requests'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #2E7D32; margin-top: 4px;">Tuân thủ Luật 91/2025/QH15</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #0D47A1; background: #F8FAFD;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #0D47A1; margin-bottom: 6px;">
                Chứng Từ Kế Toán Bảo Toàn
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #0D47A1;">
                {{ number_format($kpis['preserved_orders_count'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #0D47A1; margin-top: 4px;">100% hồ sơ thuế không bị xóa</div>
        </div>
    </div>

    {{-- Alert Feedback --}}
    @if(session('success'))
        <div class="mayhen_admin_alert mayhen_admin_alert--success" style="margin-bottom: 20px; padding: 14px 18px; border-radius: 8px; background: #E8F5E9; border: 1px solid #C8E6C9; color: #2E7D32; font-size: 0.875rem;">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mayhen_admin_alert mayhen_admin_alert--danger" style="margin-bottom: 20px; padding: 14px 18px; border-radius: 8px; background: #FFEBEE; border: 1px solid #FFCDD2; color: #C62828; font-size: 0.875rem;">
            ⚠ {{ session('error') }}
        </div>
    @endif

    {{-- Section 1: DSAR Erasure & Right to be Forgotten Desk --}}
    <div class="mayhen_admin_card" style="margin-bottom: 28px;">
        <div class="mayhen_admin_card_header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <div class="mayhen_admin_card_title" style="font-size: 1rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                    📋 Danh Sách Yêu Cầu Quên / Xóa Dữ Liệu Cá Nhân (DSAR Erasure Desk)
                </div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-top: 2px;">
                    Quy trình kiểm soát 2 bước (Dual Approval): Người yêu cầu &rarr; Quản lý duyệt &rarr; Thực thi ẩn danh hóa bảo toàn chứng từ kế toán.
                </div>
            </div>

            {{-- Filter Status Tabs --}}
            <div style="display: flex; gap: 6px;">
                <a href="{{ route('admin.privacy.index', ['status' => 'all']) }}" class="mayhen_admin_btn mayhen_admin_btn_sm {{ $currentStatus === 'all' ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn_outline' }}">
                    Tất cả
                </a>
                <a href="{{ route('admin.privacy.index', ['status' => 'pending']) }}" class="mayhen_admin_btn mayhen_admin_btn_sm {{ $currentStatus === 'pending' ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn_outline' }}">
                    Chờ duyệt ({{ $kpis['pending_requests'] }})
                </a>
                <a href="{{ route('admin.privacy.index', ['status' => 'approved']) }}" class="mayhen_admin_btn mayhen_admin_btn_sm {{ $currentStatus === 'approved' ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn_outline' }}">
                    Đã duyệt
                </a>
                <a href="{{ route('admin.privacy.index', ['status' => 'executed']) }}" class="mayhen_admin_btn mayhen_admin_btn_sm {{ $currentStatus === 'executed' ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn_outline' }}">
                    Đã ẩn danh
                </a>
            </div>
        </div>

        <div class="mayhen_admin_card_body" style="padding: 0;">
            @if($requests->isEmpty())
                <div style="text-align: center; padding: 40px 20px; color: var(--mh-admin-muted); font-size: 0.875rem;">
                    Không có yêu cầu xóa dữ liệu cá nhân nào trong mục này.
                </div>
            @else
                <div class="mayhen_admin_table_responsive">
                    <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                        <thead>
                            <tr style="background: #FDFBF7; border-bottom: 1px solid var(--mh-admin-border); text-align: left;">
                                <th style="padding: 12px 16px;">Mã DSAR</th>
                                <th style="padding: 12px 16px;">Người Yêu Cầu</th>
                                <th style="padding: 12px 16px;">Lý Do / Căn Cứ</th>
                                <th style="padding: 12px 16px;">Phạm Vi</th>
                                <th style="padding: 12px 16px;">Trạng Thái</th>
                                <th style="padding: 12px 16px;">Người Duyệt</th>
                                <th style="padding: 12px 16px; text-align: right;">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $req)
                                <tr style="border-bottom: 1px solid var(--mh-admin-border);">
                                    <td style="padding: 12px 16px; font-family: monospace; font-weight: 700; color: var(--mh-admin-gold);">
                                        {{ $req->request_code }}
                                    </td>
                                    <td style="padding: 12px 16px;">
                                        <strong>{{ $req->requester_name }}</strong>
                                        <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">{{ $req->requester_email }}</div>
                                    </td>
                                    <td style="padding: 12px 16px; max-width: 260px;">
                                        <div style="font-size: 0.8125rem; color: var(--mh-admin-charcoal); line-height: 1.4;">
                                            {{ Str::limit($req->reason, 80) }}
                                        </div>
                                        <div style="font-size: 0.7188rem; color: var(--mh-admin-muted); margin-top: 2px;">
                                            {{ $req->created_at->format('d/m/Y H:i') }}
                                        </div>
                                    </td>
                                    <td style="padding: 12px 16px;">
                                        @switch($req->retention_scope)
                                            @case('guest_pii_only')
                                                <span class="mayhen_admin_badge mayhen_admin_badge--neutral">Chỉ PII Khách</span>
                                                @break
                                            @case('full_wedding_anonymize')
                                                <span class="mayhen_admin_badge mayhen_admin_badge--info">Đám cưới cụ thể</span>
                                                @break
                                            @default
                                                <span class="mayhen_admin_badge mayhen_admin_badge--warning">Toàn bộ tài khoản</span>
                                        @endswitch
                                    </td>
                                    <td style="padding: 12px 16px;">
                                        <span class="mayhen_admin_badge {{ $req->status_badge_class }}">
                                            {{ $req->status_label }}
                                        </span>
                                    </td>
                                    <td style="padding: 12px 16px; font-size: 0.8125rem; color: var(--mh-admin-charcoal);">
                                        @if($req->reviewer)
                                            <div>✓ {{ $req->reviewer->name }}</div>
                                            <div style="font-size: 0.6875rem; color: var(--mh-admin-muted);">{{ $req->reviewed_at?->format('d/m/Y H:i') }}</div>
                                        @else
                                            <span style="color: var(--mh-admin-muted);">Chưa duyệt</span>
                                        @endif
                                    </td>
                                    <td style="padding: 12px 16px; text-align: right;">
                                        <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                                            @if($req->status === 'pending')
                                                <form action="{{ route('admin.privacy.approve_erasure', $req->id) }}" method="POST" onsubmit="return confirm('Xác nhận phê duyệt yêu cầu xóa/ẩn danh hóa này?');">
                                                    @csrf
                                                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary mayhen_admin_btn_sm">
                                                        ✓ Duyệt
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.privacy.reject_erasure', $req->id) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    <input type="hidden" name="review_notes" value="Không đáp ứng đủ điều kiện theo quy định lưu trữ.">
                                                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm" style="color: #C62828;" onclick="let r = prompt('Nhập lý do từ chối:'); if(!r) return false; this.previousElementSibling.value = r;">
                                                        ✕ Từ chối
                                                    </button>
                                                </form>
                                            @elseif($req->status === 'approved')
                                                <form action="{{ route('admin.privacy.execute_erasure', $req->id) }}" method="POST" onsubmit="return confirm('CẢNH BÁO: Hành động này sẽ ẩn danh hóa vĩnh viễn dữ liệu tài khoản nhưng bảo toàn chứng từ kế toán. Tiếp tục thực thi?');">
                                                    @csrf
                                                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_sm" style="background: #D97706; color: #fff; border: none;">
                                                        ⚡ Thực Thi Ẩn Danh
                                                    </button>
                                                </form>
                                            @elseif($req->status === 'executed')
                                                <span style="font-size: 0.75rem; color: #2E7D32; font-weight: 600;">
                                                    ✓ Đã khép kín
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($requests->hasPages())
                    <div style="padding: 16px;">
                        {{ $requests->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Section 2: Data Retention Policy Scanner (180 Days Threshold) --}}
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <div class="mayhen_admin_card_title" style="font-size: 1rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                    🧹 Quét Tự Động Lưu Trữ Dữ Liệu Khách Mời (Ngưỡng 180 Ngày Sau Cưới)
                </div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-top: 2px;">
                    Tự động dọn dẹp số điện thoại, địa chỉ IP và ghi chú dị ứng cá nhân của khách mời sau 180 ngày kể từ ngày cưới để tuân thủ quyền riêng tư.
                </div>
            </div>
        </div>

        <div class="mayhen_admin_card_body">
            @if($eligibleWeddings->isEmpty())
                <div style="text-align: center; padding: 32px 20px; color: #2E7D32; font-size: 0.875rem;">
                    ✓ Tuyệt vời! Toàn bộ tiệc cưới đã qua 180 ngày đều đã được dọn dẹp và ẩn danh hóa dữ liệu khách mời an toàn.
                </div>
            @else
                <form action="{{ route('admin.privacy.batch_retention') }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn dọn dẹp và ẩn danh hóa PII cho các tiệc cưới đã chọn? Số liệu đơn hàng kế toán sẽ được bảo toàn nguyên vẹn.');">
                    @csrf

                    <div style="margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.8125rem; color: var(--mh-admin-charcoal); font-weight: 600;">
                            Phát hiện {{ $eligibleWeddings->count() }} tiệc cưới cần dọn dẹp PII:
                        </span>

                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary mayhen_admin_btn_sm">
                            ⚡ Chạy Dọn Dẹp Các Mục Đã Chọn
                        </button>
                    </div>

                    <div class="mayhen_admin_table_responsive">
                        <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                            <thead>
                                <tr style="background: #FDFBF7; border-bottom: 1px solid var(--mh-admin-border); text-align: left;">
                                    <th style="padding: 10px 14px; width: 40px;">
                                        <input type="checkbox" id="check_all_retention" checked onclick="document.querySelectorAll('.retention_chk').forEach(c => c.checked = this.checked);">
                                    </th>
                                    <th style="padding: 10px 14px;">Đám Cưới</th>
                                    <th style="padding: 10px 14px;">Ngày Cưới</th>
                                    <th style="padding: 10px 14px;">Thời Gian Đã Qua</th>
                                    <th style="padding: 10px 14px;">Chủ Tài Khoản</th>
                                    <th style="padding: 10px 14px;">SĐT Khách Mời Cần Xóa</th>
                                    <th style="padding: 10px 14px;">Phản Hồi PII / IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($eligibleWeddings as $ew)
                                    <tr style="border-bottom: 1px solid var(--mh-admin-border);">
                                        <td style="padding: 10px 14px;">
                                            <input type="checkbox" name="wedding_ids[]" value="{{ $ew['wedding_id'] }}" class="retention_chk" checked>
                                        </td>
                                        <td style="padding: 10px 14px; font-weight: 600; color: var(--mh-admin-charcoal);">
                                            {{ $ew['wedding_name'] }}
                                        </td>
                                        <td style="padding: 10px 14px; color: var(--mh-admin-muted);">
                                            {{ $ew['wedding_date'] }}
                                        </td>
                                        <td style="padding: 10px 14px;">
                                            <span style="color: #D97706; font-weight: 600;">{{ $ew['days_since_wedding'] }} ngày trước</span>
                                        </td>
                                        <td style="padding: 10px 14px;">
                                            {{ $ew['user_name'] }} ({{ $ew['user_email'] }})
                                        </td>
                                        <td style="padding: 10px 14px; color: #C62828; font-weight: 600;">
                                            {{ $ew['guests_with_phone_count'] }} SĐT
                                        </td>
                                        <td style="padding: 10px 14px; color: #E65100;">
                                            {{ $ew['responses_pii_count'] }} bản ghi
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection

