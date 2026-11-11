@extends('admin.layouts.master')

@section('title', 'Quản Trị Chiến Dịch Tự Động Hóa Vòng Đời Cưới')

@section('content')
<div class="mayhen_admin_automation_cockpit">
    <!-- Header Section -->
    <div class="mayhen_admin_page_header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="mayhen_admin_badge mayhen_admin_badge--gold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Lifecycle Drip & Automation</span>
                <span style="color: var(--admin-text-muted); font-size: 0.85rem;">Mục 149 PRD V3 & ADM-AUTO-01</span>
            </div>
            <h1 style="font-size: 1.6rem; font-weight: 600; color: var(--admin-charcoal); margin: 0;">
                Quản Trị Chiến Dịch Tự Động Hóa Vòng Đời Cưới
            </h1>
            <p style="margin: 0.25rem 0 0 0; color: var(--admin-text-muted); font-size: 0.9rem;">
                Tự động hóa chuỗi chăm sóc dâu rể theo mốc thời gian ngày cưới (D-30 đến D+30) qua Zalo ZNS, SMS Brandname và Email.
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('admin.email-templates.index') }}" class="mayhen_admin_btn mayhen_admin_btn--outline" style="font-size: 0.85rem;">
                Mẫu Tin Nhắn
            </a>
            <a href="{{ route('admin.campaigns.automation.index', ['tab' => 'scanner']) }}" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="font-size: 0.85rem;">
                ⚡ Phòng Điều Hành Quét
            </a>
        </div>
    </div>

    @if(session('scan_result'))
    @php $res = session('scan_result'); @endphp
    <div class="mayhen_admin_card" style="padding: 1.25rem; margin-bottom: 1.5rem; background: #F8FAFC; border-left: 4px solid var(--admin-gold);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
            <strong style="color: var(--admin-charcoal); font-size: 1rem;">
                Kết Quả Quét Tự Động Hóa: {{ $res['matched_weddings'] ?? 0 }} Tiệc Cưới Khớp Mốc
            </strong>
            <span class="mayhen_admin_badge mayhen_admin_badge--gold">
                {{ ($res['dispatched'] ?? 0) }} phát tin &bull; {{ ($res['skipped'] ?? 0) }} bỏ qua trùng lặp
            </span>
        </div>
        <p style="margin: 0; font-size: 0.85rem; color: var(--admin-text-muted);">
            Đã hoàn thành rà soát {{ $res['scanned_rules'] ?? 0 }} kịch bản vòng đời. Xem chi tiết tại tab Sổ Nhật Ký Tự Động Hóa.
        </p>
    </div>
    @endif

    <!-- 4 KPI Cards -->
    <div class="mayhen_admin_kpi_grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid #7C3AED;">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--admin-text-muted); text-transform: uppercase;">Kịch Bản Vòng Đời</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--admin-charcoal); margin: 0.4rem 0 0.25rem 0;">
                {{ number_format($kpis['total_rules']) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Chuỗi tương tác D-30 đến D+30</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid #059669;">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--admin-text-muted); text-transform: uppercase;">Đang Kích Hoạt</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #059669; margin: 0.4rem 0 0.25rem 0;">
                {{ number_format($kpis['active_rules']) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Tự động quét hàng ngày theo lịch trình</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid var(--admin-gold);">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--admin-text-muted); text-transform: uppercase;">Phát Tin 24h Qua</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--admin-charcoal); margin: 0.4rem 0 0.25rem 0;">
                {{ number_format($kpis['dispatched_24h']) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Thông điệp gửi tới dâu rể đúng thời điểm</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid #2563EB;">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--admin-text-muted); text-transform: uppercase;">Khớp Mốc Hôm Nay</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #2563EB; margin: 0.4rem 0 0.25rem 0;">
                {{ number_format($kpis['ready_today']) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Tiệc cưới sẵn sàng nhận thông điệp</div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div style="display: flex; gap: 0.5rem; border-bottom: 1px solid var(--admin-border-color); margin-bottom: 1.5rem;">
        <a href="{{ route('admin.campaigns.automation.index', ['tab' => 'rules']) }}" 
           class="mayhen_admin_tab {{ $currentTab === 'rules' ? 'mayhen_admin_tab--active' : '' }}"
           style="padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'rules' ? 'var(--admin-gold)' : 'transparent' }}; color: {{ $currentTab === 'rules' ? 'var(--admin-gold)' : 'var(--admin-text-muted)' }};">
            Kịch Bản Vòng Đời Cưới ({{ count($rules) }})
        </a>
        <a href="{{ route('admin.campaigns.automation.index', ['tab' => 'scanner']) }}" 
           class="mayhen_admin_tab {{ $currentTab === 'scanner' ? 'mayhen_admin_tab--active' : '' }}"
           style="padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'scanner' ? 'var(--admin-gold)' : 'transparent' }}; color: {{ $currentTab === 'scanner' ? 'var(--admin-gold)' : 'var(--admin-text-muted)' }};">
            Phòng Điều Hành Quét Tự Động
        </a>
        <a href="{{ route('admin.campaigns.automation.index', ['tab' => 'logs']) }}" 
           class="mayhen_admin_tab {{ $currentTab === 'logs' ? 'mayhen_admin_tab--active' : '' }}"
           style="padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $currentTab === 'logs' ? 'var(--admin-gold)' : 'transparent' }}; color: {{ $currentTab === 'logs' ? 'var(--admin-gold)' : 'var(--admin-text-muted)' }};">
            Sổ Nhật Ký Tự Động Hóa ({{ count($logs) }})
        </a>
    </div>

    @if($currentTab === 'rules')
    <!-- TAB 1: KỊCH BẢN VÒNG ĐỜI CƯỚI -->
    <div class="mayhen_admin_card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--admin-border-color); padding-bottom: 1rem;">
            <div>
                <h2 style="font-size: 1.15rem; font-weight: 600; color: var(--admin-charcoal); margin: 0 0 0.25rem 0;">
                    Chuỗi Kịch Bản Tự Động Hóa (D-Day Milestone Timeline)
                </h2>
                <p style="margin: 0; color: var(--admin-text-muted); font-size: 0.85rem;">
                    Hệ thống tự động kích hoạt vào khung giờ quy định mỗi ngày dựa trên mốc ngày cưới chính thức của cặp đôi.
                </p>
            </div>
            <span class="mayhen_admin_badge mayhen_admin_badge--gold">Quiet Luxury SOP Automation</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--admin-border-color); text-align: left; color: var(--admin-text-muted); font-size: 0.8rem; text-transform: uppercase;">
                        <th style="padding: 0.75rem 1rem;">Mốc Thời Gian</th>
                        <th style="padding: 0.75rem 1rem;">Tên Kịch Bản & Mô Tả</th>
                        <th style="padding: 0.75rem 1rem;">Kênh Gửi</th>
                        <th style="padding: 0.75rem 1rem;">Giờ Gửi</th>
                        <th style="padding: 0.75rem 1rem;">Lượt Chạy</th>
                        <th style="padding: 0.75rem 1rem;">Trạng Thái</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                    <tr style="border-bottom: 1px solid var(--admin-border-color); vertical-align: middle;">
                        <td style="padding: 1rem;">
                            @php
                                $offset = $rule->offset_days;
                                $badgeBg = $offset < 0 ? '#2563EB' : ($offset === 0 ? '#DC2626' : '#7C3AED');
                            @endphp
                            <span style="display: inline-block; padding: 0.35rem 0.65rem; border-radius: 4px; font-weight: 700; font-size: 0.85rem; color: #fff; background-color: {{ $badgeBg }};">
                                {{ $rule->offset_label }}
                            </span>
                        </td>
                        <td style="padding: 1rem;">
                            <div style="font-weight: 600; color: var(--admin-charcoal); font-size: 0.95rem;">
                                {{ $rule->name }}
                            </div>
                            <div style="color: var(--admin-text-muted); font-size: 0.825rem; margin-top: 0.2rem;">
                                {{ $rule->description ?? 'Không có mô tả bổ sung.' }}
                            </div>
                            <div style="margin-top: 0.35rem;">
                                <code style="font-size: 0.75rem; color: #64748B; background: #F1F5F9; padding: 0.15rem 0.4rem; border-radius: 3px;">
                                    Mã: {{ $rule->code }} &bull; Template: {{ $rule->template_code ?? 'N/A' }}
                                </code>
                            </div>
                        </td>
                        <td style="padding: 1rem;">
                            @php
                                $chLabels = [
                                    'email' => 'Email',
                                    'zalo_zns' => 'Zalo ZNS',
                                    'sms_brandname' => 'SMS Brandname',
                                    'in_app' => 'In-App',
                                ];
                            @endphp
                            <span class="mayhen_admin_badge mayhen_admin_badge--outline" style="font-size: 0.8rem;">
                                {{ $chLabels[$rule->channel] ?? $rule->channel }}
                            </span>
                        </td>
                        <td style="padding: 1rem; font-size: 0.85rem; color: var(--admin-charcoal);">
                            {{ $rule->send_time }}
                        </td>
                        <td style="padding: 1rem; font-size: 0.85rem;">
                            <span style="font-weight: 600; color: var(--admin-charcoal);">{{ number_format($rule->execution_count) }}</span>
                            @if($rule->last_executed_at)
                                <div style="font-size: 0.75rem; color: var(--admin-text-muted);">
                                    Gần nhất: {{ $rule->last_executed_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </td>
                        <td style="padding: 1rem;">
                            @if($rule->is_active)
                                <span class="mayhen_admin_badge" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0;">
                                    ● Đang Bật
                                </span>
                            @else
                                <span class="mayhen_admin_badge" style="background: #F1F5F9; color: #64748B;">
                                    ○ Tạm Dừng
                                </span>
                            @endif
                        </td>
                        <td style="padding: 1rem; text-align: right;">
                            <div style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                <form method="POST" action="{{ route('admin.campaigns.automation.toggle', $rule) }}" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--sm {{ $rule->is_active ? 'mayhen_admin_btn--outline' : 'mayhen_admin_btn--primary' }}" style="font-size: 0.75rem; padding: 0.3rem 0.6rem;">
                                        {{ $rule->is_active ? 'Tạm Dừng' : 'Kích Hoạt' }}
                                    </button>
                                </form>
                                <button type="button" 
                                        class="mayhen_admin_btn mayhen_admin_btn--secondary mayhen_admin_btn--sm" 
                                        style="font-size: 0.75rem; padding: 0.3rem 0.6rem;"
                                        onclick="openEditRuleModal({{ json_encode($rule) }})">
                                    Sửa
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 3rem; text-align: center; color: var(--admin-text-muted);">
                            Chưa có kịch bản vòng đời nào được thiết lập.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($currentTab === 'scanner')
    <!-- TAB 2: PHÒNG ĐIỀU HÀNH QUÉT TỰ ĐỘNG -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
        <!-- Left: Bộ điều khiển quét tự công -->
        <div class="mayhen_admin_card" style="padding: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                <span class="mayhen_admin_badge mayhen_admin_badge--gold">Command Scanner</span>
                <h2 style="font-size: 1.15rem; font-weight: 600; color: var(--admin-charcoal); margin: 0;">
                    Kích Hoạt Quét Chu Kỳ Vòng Đời
                </h2>
            </div>
            <p style="color: var(--admin-text-muted); font-size: 0.85rem; line-height: 1.5; margin-bottom: 1.25rem;">
                Mỗi ngày hệ thống tự động chạy background job quét toàn bộ tiệc cưới để gửi tin theo mốc D-Day. Bạn có thể kích hoạt quét cưỡng chế bằng tay hoặc chạy thử nghiệm (Dry-Run) an toàn bên dưới.
            </p>

            <form method="POST" action="{{ route('admin.campaigns.automation.scan') }}" style="display: flex; flex-direction: column; gap: 1.25rem;">
                @csrf
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--admin-charcoal); margin-bottom: 0.4rem;">
                        Chọn Kịch Bản Cụ Thể (Tùy chọn)
                    </label>
                    <select name="rule_id" class="mayhen_admin_input" style="width: 100%;">
                        <option value="">-- Quét tất cả kịch bản đang kích hoạt --</option>
                        @foreach($rules as $r)
                            <option value="{{ $r->id }}">{{ $r->offset_label }}: {{ $r->name }} ({{ $r->channel }})</option>
                        @endforeach
                    </select>
                </div>

                <div style="padding: 1rem; background: #F8FAFC; border: 1px solid var(--admin-border-color); border-radius: 6px;">
                    <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer; margin: 0;">
                        <input type="checkbox" name="dry_run" value="1" checked style="margin-top: 0.2rem;">
                        <div>
                            <span style="font-size: 0.875rem; font-weight: 600; color: var(--admin-charcoal);">Chế độ thử nghiệm an toàn (Dry Run - Preview Only)</span>
                            <p style="margin: 0.2rem 0 0 0; font-size: 0.8rem; color: var(--admin-text-muted);">
                                Khi bật, hệ thống chỉ tính toán và xuất log preview các tiệc cưới đủ điều kiện gửi tin mà KHÔNG bắn tin Zalo/SMS/Email thật đến người dùng.
                            </p>
                        </div>
                    </label>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem;">
                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="display: flex; align-items: center; gap: 0.4rem;">
                        <span>🚀</span> Bắt Đầu Quét Chu Kỳ Vòng Đời
                    </button>
                </div>
            </form>
        </div>

        <!-- Right: Tiêu chuẩn thiết kế & Idempotency Rules -->
        <div class="mayhen_admin_card" style="padding: 1.5rem; background: #FAF9F6; border: 1px solid #E5E7EB;">
            <h2 style="font-size: 1.15rem; font-weight: 600; color: var(--admin-charcoal); margin: 0 0 0.75rem 0;">
                Nguyên Tắc Bất Biến Vòng Đời Cưới (Mây Hẹn Quiet Luxury)
            </h2>
            <ul style="margin: 0; padding-left: 1.25rem; color: #475569; font-size: 0.85rem; line-height: 1.6;">
                <li style="margin-bottom: 0.6rem;">
                    <strong>Ngăn Chặn Gửi Trùng (Idempotency Key):</strong> Mỗi cặp đôi với 1 kịch bản cụ thể chỉ nhận tối đa 1 thông điệp. Hệ thống tự động bỏ qua nếu đã gửi hoặc mô phỏng trước đó.
                </li>
                <li style="margin-bottom: 0.6rem;">
                    <strong>Bảo Vệ Dữ Liệu Cá Nhân (Law 91/2025/QH15):</strong> Toàn bộ số điện thoại và email trong bảng nhật ký (logs) đều được che giấu PII (ví dụ: <code>098****321</code>, <code>d****@gmail.com</code>).
                </li>
                <li style="margin-bottom: 0.6rem;">
                    <strong>Khung Giờ Tinh Tế:</strong> Mặc định cấu hình gửi lúc 09:00 sáng để tôn trọng sự riêng tư và tạo trải nghiệm cao cấp cho dâu rể.
                </li>
                <li>
                    <strong>Tích Hợp Đa Kênh:</strong> Dễ dàng phối hợp giữa Email, Zalo ZNS và SMS Brandname để tối ưu tỷ lệ tiếp cận mà không gây phiền toái.
                </li>
            </ul>
        </div>
    </div>
    @endif

    @if($currentTab === 'logs')
    <!-- TAB 3: SỔ NHẬT KÝ TỰ ĐỘNG HÓA -->
    <div class="mayhen_admin_card" style="padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--admin-border-color); padding-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="font-size: 1.15rem; font-weight: 600; color: var(--admin-charcoal); margin: 0 0 0.25rem 0;">
                    Sổ Nhật Ký Tự Động Hóa (Automation Audit Logs)
                </h2>
                <p style="margin: 0; color: var(--admin-text-muted); font-size: 0.85rem;">
                    Lưu trữ minh bạch 30 tác vụ quét gần nhất, trạng thái kích hoạt, thời gian thực thi và phản hồi cổng viễn thông.
                </p>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--admin-border-color); text-align: left; color: var(--admin-text-muted); font-size: 0.8rem; text-transform: uppercase;">
                        <th style="padding: 0.75rem 1rem;">Thời Điểm</th>
                        <th style="padding: 0.75rem 1rem;">Kịch Bản Vòng Đời</th>
                        <th style="padding: 0.75rem 1rem;">Đám Cưới / Cặp Đôi</th>
                        <th style="padding: 0.75rem 1rem;">Người Nhận (Protected PII)</th>
                        <th style="padding: 0.75rem 1rem;">Kênh</th>
                        <th style="padding: 0.75rem 1rem;">Trạng Thái</th>
                        <th style="padding: 0.75rem 1rem;">Nội Dung Tóm Tắt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr style="border-bottom: 1px solid var(--admin-border-color); vertical-align: middle;">
                        <td style="padding: 0.75rem 1rem; color: var(--admin-text-muted); font-size: 0.8rem; white-space: nowrap;">
                            {{ $log->dispatched_at ? $log->dispatched_at->format('d/m/Y H:i:s') : $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            <div style="font-weight: 600; color: var(--admin-charcoal);">
                                {{ $log->rule->name ?? 'N/A' }}
                            </div>
                            <code style="font-size: 0.75rem; color: #64748B;">
                                {{ $log->rule->offset_label ?? '' }}
                            </code>
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            @if($log->wedding)
                                <div style="font-weight: 500; color: var(--admin-charcoal);">
                                    {{ $log->wedding->groom_name }} & {{ $log->wedding->bride_name }}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--admin-text-muted);">
                                    Ngày cưới: {{ $log->wedding->wedding_date ? \Carbon\Carbon::parse($log->wedding->wedding_date)->format('d/m/Y') : 'N/A' }}
                                </div>
                            @else
                                <span style="color: var(--admin-text-muted);">Tiệc #{{ $log->wedding_id }}</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem; font-size: 0.85rem; color: #334155;">
                            {{ $log->recipient }}
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            <span class="mayhen_admin_badge mayhen_admin_badge--outline" style="font-size: 0.75rem; text-transform: uppercase;">
                                {{ $log->channel }}
                            </span>
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            @if($log->status === 'dispatched')
                                <span class="mayhen_admin_badge" style="background: #ECFDF5; color: #059669;">Đã Phát Tin</span>
                            @elseif($log->status === 'simulated')
                                <span class="mayhen_admin_badge" style="background: #EFF6FF; color: #2563EB;">Mô Phỏng Dry-Run</span>
                            @elseif($log->status === 'failed')
                                <span class="mayhen_admin_badge" style="background: #FEF2F2; color: #DC2626;">Lỗi Gửi</span>
                            @else
                                <span class="mayhen_admin_badge" style="background: #F1F5F9; color: #64748B;">Bỏ Qua</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem; color: var(--admin-text-muted); font-size: 0.8rem; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $log->message_summary ?? 'Không có chi tiết' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 3rem; text-align: center; color: var(--admin-text-muted);">
                            Chưa có nhật ký tự động hóa nào được ghi nhận.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<!-- Modal Chỉnh Sửa Kịch Bản Vòng Đời -->
<div id="mayhen_edit_rule_modal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.4); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div class="mayhen_admin_card" style="width: 100%; max-width: 580px; padding: 1.5rem; background: #fff; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); margin: 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--admin-border-color); padding-bottom: 0.75rem;">
            <h3 style="font-size: 1.15rem; font-weight: 600; color: var(--admin-charcoal); margin: 0;">
                Chỉnh Sửa Kịch Bản Tự Động Hóa
            </h3>
            <button type="button" onclick="closeEditRuleModal()" style="border: none; background: transparent; font-size: 1.25rem; cursor: pointer; color: var(--admin-text-muted);">&times;</button>
        </div>

        <form id="mayhen_edit_rule_form" method="POST" action="">
            @csrf
            @method('PUT')
            
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--admin-charcoal); margin-bottom: 0.35rem;">
                        Tên Kịch Bản
                    </label>
                    <input type="text" id="modal_rule_name" name="name" class="mayhen_admin_input" style="width: 100%;" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--admin-charcoal); margin-bottom: 0.35rem;">
                            Mốc Ngày (Offset Days)
                        </label>
                        <input type="number" id="modal_rule_offset_days" name="offset_days" class="mayhen_admin_input" style="width: 100%;" placeholder="-30 hoặc 1" required>
                        <small style="color: var(--admin-text-muted); font-size: 0.75rem;">Số âm: trước cưới; Số dương: sau cưới.</small>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--admin-charcoal); margin-bottom: 0.35rem;">
                            Giờ Gửi Trong Ngày
                        </label>
                        <input type="text" id="modal_rule_send_time" name="send_time" class="mayhen_admin_input" style="width: 100%;" placeholder="09:00" required>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--admin-charcoal); margin-bottom: 0.35rem;">
                        Kênh Phát Tin
                    </label>
                    <select id="modal_rule_channel" name="channel" class="mayhen_admin_input" style="width: 100%;" required>
                        <option value="email">Email Marketing / Thông Báo</option>
                        <option value="zalo_zns">Zalo ZNS (Notification Service)</option>
                        <option value="sms_brandname">SMS Brandname Viễn Thông</option>
                        <option value="in_app">Thông Báo Trong Ứng Dụng (In-App)</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--admin-charcoal); margin-bottom: 0.35rem;">
                        Mô Tả Kịch Bản
                    </label>
                    <textarea id="modal_rule_description" name="description" class="mayhen_admin_input" rows="2" style="width: 100%;"></textarea>
                </div>

                <div style="margin-top: 0.5rem; display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--admin-border-color); padding-top: 1rem;">
                    <button type="button" class="mayhen_admin_btn mayhen_admin_btn--outline" onclick="closeEditRuleModal()">
                        Hủy Bỏ
                    </button>
                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary">
                        Lưu Cấu Hình Kịch Bản
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openEditRuleModal(rule) {
    const modal = document.getElementById('mayhen_edit_rule_modal');
    const form = document.getElementById('mayhen_edit_rule_form');
    
    form.action = `/admin/campaigns/automation/rules/${rule.id}`;
    document.getElementById('modal_rule_name').value = rule.name || '';
    document.getElementById('modal_rule_offset_days').value = rule.offset_days || 0;
    document.getElementById('modal_rule_send_time').value = rule.send_time || '09:00';
    document.getElementById('modal_rule_channel').value = rule.channel || 'email';
    document.getElementById('modal_rule_description').value = rule.description || '';

    modal.style.display = 'flex';
}

function closeEditRuleModal() {
    const modal = document.getElementById('mayhen_edit_rule_modal');
    modal.style.display = 'none';
}
</script>
@endsection
