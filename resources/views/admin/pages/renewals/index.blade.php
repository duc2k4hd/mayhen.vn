@extends('admin.layouts.master')

@section('title', 'Quản Trị Gia Hạn & Hết Hạn Thiệp Cưới')
@section('page_title', 'Quản Trị Gia Hạn, Hết Hạn Thiệp Cưới & Thời Gian Ân Hạn (Grace Period Monitor)')

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
        .mayhen_modal {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .mayhen_modal_content {
            background: #FFFFFF;
            border-radius: 12px;
            max-width: 500px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
    </style>
@endpush

@section('content')
    <!-- Intro Card -->
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 20px 24px; border-left: 4px solid var(--mh-admin-primary, #A38048);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h3 style="font-size: 1.25rem; margin-bottom: 4px; color: var(--mh-admin-charcoal, #2B2927);">
                    Giám Sát Vòng Đời, Hạn Dùng & Ân Hạn Thiệp Cưới (Mục 139 PRD V3 & Mục 13.4 DOCX)
                </h3>
                <p style="color: var(--mh-admin-muted, #7A756D); font-size: 0.84375rem; margin: 0;">
                    Chính sách ân hạn {{ $graceDays }} ngày bảo toàn nguyên vẹn dữ liệu thiệp cưới sau ngày hết hạn. Hỗ trợ gia hạn thủ công có ghi vết kiểm toán và tự động nhắc nhở cặp đôi.
                </p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <span class="mayhen_admin_badge" style="background: #FFF8E1; color: #F57F17; padding: 6px 12px; font-size: 0.75rem; font-weight: 600;">
                    ⏳ Ân Hạn: {{ $graceDays }} Ngày
                </span>
                <span class="mayhen_admin_badge" style="background: #E8F5E9; color: #2E7D32; padding: 6px 12px; font-size: 0.75rem; font-weight: 600;">
                    ♾️ Hỗ Trợ Gói Trọn Đời
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

    <!-- 4 Thẻ KPI Hạn Dùng Thiệp Cưới -->
    <div class="mayhen_admin_kpi_grid">
        <div class="mayhen_admin_kpi_card">
            <div class="mayhen_admin_kpi_title">Tổng Số Thiệp Cưới</div>
            <div class="mayhen_admin_kpi_value">{{ number_format($kpis['total']) }}</div>
            <div class="mayhen_admin_kpi_meta">Toàn bộ thiệp trên hệ thống</div>
        </div>
        <div class="mayhen_admin_kpi_card">
            <div class="mayhen_admin_kpi_title">Sắp Hết Hạn (Trong 14 Ngày)</div>
            <div class="mayhen_admin_kpi_value" style="color: #F57F17;">
                {{ number_format($kpis['expiring_soon']) }}
            </div>
            <div class="mayhen_admin_kpi_meta">Cần nhắc cặp đôi gia hạn</div>
        </div>
        <div class="mayhen_admin_kpi_card">
            <div class="mayhen_admin_kpi_title">Đang Trong Thời Gian Ân Hạn</div>
            <div class="mayhen_admin_kpi_value" style="color: #E65100;">
                {{ number_format($kpis['in_grace']) }}
            </div>
            <div class="mayhen_admin_kpi_meta">Thiệp đã quá hạn &lt; {{ $graceDays }} ngày</div>
        </div>
        <div class="mayhen_admin_kpi_card">
            <div class="mayhen_admin_kpi_title">Đã Lưu Trữ / Hết Hạn</div>
            <div class="mayhen_admin_kpi_value" style="color: #5C5446;">
                {{ number_format($kpis['archived']) }}
            </div>
            <div class="mayhen_admin_kpi_meta">Chuyển sang chế độ kỷ niệm riêng tư</div>
        </div>
    </div>

    <!-- Thanh Tìm Kiếm & Lọc Trạng Thái -->
    <div class="mayhen_admin_card" style="padding: 20px 24px; margin-bottom: 24px;">
        <form method="GET" action="{{ route('admin.renewals.index') }}" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; gap: 12px; align-items: center; flex: 1; flex-wrap: wrap;">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       class="mayhen_admin_input" 
                       placeholder="Tìm theo tên cô dâu, chú rể, slug hoặc tiêu đề..." 
                       style="min-width: 260px; max-width: 380px;">

                <select name="status" class="mayhen_admin_select" onchange="this.form.submit()">
                    <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>Tất cả trạng thái hạn dùng</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="expiring_soon" {{ request('status') === 'expiring_soon' ? 'selected' : '' }}>Sắp hết hạn (14 ngày)</option>
                    <option value="in_grace_period" {{ request('status') === 'in_grace_period' ? 'selected' : '' }}>Đang trong ân hạn (Grace Period)</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Đã lưu trữ / Hết hạn</option>
                    <option value="lifetime" {{ request('status') === 'lifetime' ? 'selected' : '' }}>Lưu trữ trọn đời (VIP)</option>
                </select>

                <button type="submit" class="mayhen_admin_btn" style="background: #F4F1EA; border: 1px solid #D6D0C4; color: #5A554E; padding: 9px 16px; border-radius: 6px; font-size: 0.8125rem;">
                    Lọc Dữ Liệu
                </button>
            </div>

            <div>
                <a href="{{ route('admin.renewals.export') }}" class="mayhen_admin_btn" style="background: #FAF8F5; border: 1px solid #D6D0C4; color: var(--mh-admin-charcoal, #2B2927); padding: 9px 18px; border-radius: 6px; text-decoration: none; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 6px;">
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

    <!-- Bảng Danh Sách Thiệp Cưới & Theo Dõi Hạn Dùng -->
    <div class="mayhen_admin_card" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h4 style="font-size: 1.15rem; margin: 0; color: var(--mh-admin-charcoal, #2B2927);">
                    Danh Sách Thiệp Cưới & Tình Trạng Lưu Trữ
                </h4>
                <p style="color: var(--mh-admin-muted, #7A756D); font-size: 0.8125rem; margin: 2px 0 0;">
                    Chủ động gia hạn lưu trữ, gửi thư nhắc nhở hoặc khôi phục thiệp cưới từ kho lưu trữ kỷ niệm.
                </p>
            </div>
        </div>

        @if($invitations->isEmpty())
            <div style="padding: 48px; text-align: center; color: var(--mh-admin-muted, #7A756D); font-size: 0.875rem;">
                Không tìm thấy thiệp cưới nào phù hợp với bộ lọc hiện tại.
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="mayhen_admin_table" style="width: 100%; font-size: 0.8125rem;">
                    <thead>
                        <tr>
                            <th>Cặp Đôi & Thiệp Cưới</th>
                            <th>Chủ Sở Hữu</th>
                            <th style="width: 140px;">Mẫu Thiệp</th>
                            <th style="width: 130px;">Hạn Lưu Trữ</th>
                            <th style="width: 160px;">Trạng Thái Hạn</th>
                            <th style="width: 200px; text-align: right;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invitations as $inv)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--mh-admin-charcoal, #2B2927);">
                                        {{ $inv->wedding->groom_name ?? 'Chú Rể' }} &amp; {{ $inv->wedding->bride_name ?? 'Cô Dâu' }}
                                    </div>
                                    <div style="color: var(--mh-admin-muted, #7A756D); font-size: 0.75rem; margin-top: 2px;">
                                        <code>{{ $inv->slug }}</code>
                                    </div>
                                    <div style="margin-top: 2px;">
                                        <a href="{{ url('/thiep/' . $inv->slug) }}" target="_blank" style="color: #0277BD; font-size: 0.72rem; text-decoration: underline;">
                                            Xem thiệp công khai ↗
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 500;">
                                        {{ $inv->wedding->user->name ?? 'Người dùng ẩn danh' }}
                                    </div>
                                    <div style="color: var(--mh-admin-muted, #7A756D); font-size: 0.75rem;">
                                        {{ $inv->wedding->user->email ?? 'Không có email' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="mayhen_admin_badge" style="background: #F0ECE4; color: #4A4640; font-size: 0.7rem;">
                                        {{ $inv->template->name ?? 'Mẫu Cơ Bản' }}
                                    </span>
                                </td>
                                <td>
                                    @if($inv->expires_at)
                                        <div style="font-weight: 500;">
                                            {{ \Carbon\Carbon::parse($inv->expires_at)->format('d/m/Y') }}
                                        </div>
                                        <div style="font-size: 0.7rem; color: var(--mh-admin-muted, #7A756D);">
                                            {{ \Carbon\Carbon::parse($inv->expires_at)->diffForHumans() }}
                                        </div>
                                    @else
                                        <span style="color: #8C6B37; font-weight: 600;">Trọn Đời (VIP)</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="mayhen_admin_badge" style="background: {{ $inv->lifecycle['color'] }}15; color: {{ $inv->lifecycle['color'] }}; font-weight: 600; font-size: 0.75rem; padding: 4px 10px; border-radius: 12px; border: 1px solid {{ $inv->lifecycle['color'] }}30;">
                                        {{ $inv->lifecycle['label'] }}
                                    </span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <!-- Nút Gia Hạn (Mở Modal) -->
                                    <button type="button" 
                                            onclick="openExtendModal({{ $inv->id }}, {{ json_encode($inv->wedding->groom_name ?? 'Chú Rể') }}, {{ json_encode($inv->wedding->bride_name ?? 'Cô Dâu') }}, {{ json_encode($inv->expires_at ? \Carbon\Carbon::parse($inv->expires_at)->format('d/m/Y') : 'Trọn đời') }})"
                                            class="mayhen_admin_btn" 
                                            style="background: #FAF8F5; border: 1px solid #D6D0C4; color: #8C6B37; font-size: 0.75rem; padding: 5px 8px; border-radius: 4px; cursor: pointer;">
                                        + Gia Hạn
                                    </button>

                                    <!-- Nút Nhắc Nhở -->
                                    <form action="{{ route('admin.renewals.remind', ['id' => $inv->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Bạn có muốn gửi email nhắc gia hạn tới cặp đôi?');">
                                        @csrf
                                        <button type="submit" 
                                                class="mayhen_admin_btn" 
                                                title="Gửi email nhắc hạn" 
                                                style="background: #FAF8F5; border: 1px solid #D6D0C4; color: #0277BD; font-size: 0.75rem; padding: 5px 8px; border-radius: 4px; cursor: pointer; margin-left: 2px;">
                                            ✉ Nhắc Hạn
                                        </button>
                                    </form>

                                    <!-- Nút Lưu Trữ hoặc Khôi Phục -->
                                    @if($inv->lifecycle['status'] === \App\Services\InvitationRenewalService::STATUS_ARCHIVED)
                                        <form action="{{ route('admin.renewals.unarchive', ['id' => $inv->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Khôi phục thiệp cưới này về trạng thái hoạt động?');">
                                            @csrf
                                            <button type="submit" 
                                                    class="mayhen_admin_btn" 
                                                    style="background: #E8F5E9; border: 1px solid #A5D6A7; color: #2E7D32; font-size: 0.75rem; padding: 5px 8px; border-radius: 4px; cursor: pointer; margin-left: 2px;">
                                                ↺ Khôi Phục
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.renewals.archive', ['id' => $inv->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Chuyển thiệp cưới này sang trạng thái lưu trữ kỷ niệm riêng tư?');">
                                            @csrf
                                            <button type="submit" 
                                                    class="mayhen_admin_btn" 
                                                    style="background: #FFF3E0; border: 1px solid #FFE0B2; color: #E65100; font-size: 0.75rem; padding: 5px 8px; border-radius: 4px; cursor: pointer; margin-left: 2px;">
                                                📦 Lưu Trữ
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
                {{ $invitations->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Gia Hạn Thiệp Cưới -->
    <div id="extendModal" class="mayhen_modal" onclick="closeExtendModal(event)">
        <div class="mayhen_modal_content" onclick="event.stopPropagation()">
            <div style="padding: 16px 20px; border-bottom: 1px solid #EBE7DF; display: flex; justify-content: space-between; align-items: center;">
                <h4 style="margin: 0; font-size: 1.1rem; color: var(--mh-admin-charcoal, #2B2927);">
                    Gia Hạn Thời Hạn Lưu Trữ Thiệp Cưới
                </h4>
                <button type="button" onclick="document.getElementById('extendModal').style.display='none'" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #7A756D;">
                    &times;
                </button>
            </div>
            <form id="extendForm" method="POST" action="">
                @csrf
                <div style="padding: 20px;">
                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 0.75rem; color: var(--mh-admin-muted, #7A756D); margin-bottom: 2px;">Cặp đôi:</div>
                        <div id="modalCoupleName" style="font-weight: 600; color: var(--mh-admin-charcoal, #2B2927);"></div>
                        <div style="font-size: 0.75rem; color: #8C6B37; margin-top: 2px;" id="modalCurrentExpire"></div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label class="mayhen_admin_label" for="extend_duration" style="font-weight: 600; margin-bottom: 4px; display: block;">
                            Thời Gian Gia Hạn Thêm <span style="color: #C62828;">*</span>
                        </label>
                        <select name="duration" id="extend_duration" class="mayhen_admin_select" style="width: 100%;">
                            <option value="1_month">+ 1 Tháng (30 ngày)</option>
                            <option value="3_months" selected>+ 3 Tháng (90 ngày) — Khuyến nghị</option>
                            <option value="6_months">+ 6 Tháng (180 ngày)</option>
                            <option value="1_year">+ 1 Năm (365 ngày)</option>
                            <option value="lifetime">Trọn Đời (Lifetime VIP — Không giới hạn)</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label class="mayhen_admin_label" for="extend_reason" style="font-weight: 600; margin-bottom: 4px; display: block;">
                            Lý Do Gia Hạn (Ghi vết kiểm toán)
                        </label>
                        <input type="text" 
                               name="reason" 
                               id="extend_reason" 
                               class="mayhen_admin_input" 
                               style="width: 100%;" 
                               placeholder="VD: Chăm sóc khách hàng VIP, gia hạn sự kiện dời lịch...">
                    </div>
                </div>
                <div style="padding: 12px 20px; border-top: 1px solid #EBE7DF; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="document.getElementById('extendModal').style.display='none'" class="mayhen_admin_btn" style="background: #F4F1EA; border: 1px solid #D6D0C4; color: #5A554E; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 0.8125rem;">
                        Hủy
                    </button>
                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="background: var(--mh-admin-primary, #A38048); color: #FFFFFF; border: none; padding: 8px 18px; border-radius: 6px; font-weight: 500; font-size: 0.8125rem; cursor: pointer;">
                        Xác Nhận Gia Hạn
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openExtendModal(invitationId, groomName, brideName, currentExpire) {
            const form = document.getElementById('extendForm');
            form.action = '{{ url("admin/renewals") }}/' + invitationId + '/extend';
            document.getElementById('modalCoupleName').textContent = groomName + ' & ' + brideName;
            document.getElementById('modalCurrentExpire').textContent = 'Hạn hiện tại: ' + currentExpire;
            document.getElementById('extendModal').style.display = 'flex';
        }

        function closeExtendModal(event) {
            if (event.target.id === 'extendModal') {
                document.getElementById('extendModal').style.display = 'none';
            }
        }
    </script>
@endpush

