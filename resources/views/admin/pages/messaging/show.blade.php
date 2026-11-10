@extends('admin.layouts.master')

@section('title', 'Chi Tiết Đợt Phát Tin — ' . $dispatch->campaign_name . ' — Mây Hẹn')
@section('page_title', 'Chi Tiết Đợt Phát Tin Thiệp Mời')

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.messaging.index') }}" style="font-size: 0.85rem; color: #7A756E; text-decoration: none;">
            &larr; Quay lại danh sách chiến dịch
        </a>
    </div>

    {{-- Campaign Summary & Operational Control Cockpit --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 24px; align-items: start;">
        {{-- Left: Campaign Details Card --}}
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 22px 26px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h2 style="font-size: 1.2rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                        {{ $dispatch->campaign_name }}
                    </h2>
                    <div style="font-size: 0.8rem; color: #7A756E; margin-top: 4px;">
                        Tiệc cưới: <strong>{{ $dispatch->wedding?->groom_name }} & {{ $dispatch->wedding?->bride_name }}</strong>
                        &bull; Ngày cưới: {{ $dispatch->wedding?->wedding_date ? \Carbon\Carbon::parse($dispatch->wedding->wedding_date)->format('d/m/Y') : 'N/A' }}
                    </div>
                </div>

                <div>
                    @if($dispatch->status === 'completed')
                        <span style="background: #E8F8F5; color: #117864; font-size: 0.75rem; font-weight: 700; padding: 6px 12px; border-radius: 6px; border: 1px solid #A3E4D7;">
                            ✓ ĐÃ PHÁT TIN THÀNH CÔNG
                        </span>
                    @elseif($dispatch->status === 'dry_run_ready')
                        <span style="background: #FEF9E7; color: #B7950B; font-size: 0.75rem; font-weight: 700; padding: 6px 12px; border-radius: 6px; border: 1px solid #F9E79F;">
                            ĐÃ DRY-RUN (SẴN SÀNG DUYỆT GỬI)
                        </span>
                    @elseif($dispatch->status === 'processing')
                        <span style="background: #EBF5FB; color: #2980B9; font-size: 0.75rem; font-weight: 700; padding: 6px 12px; border-radius: 6px;">
                            ĐANG GỬI TIN...
                        </span>
                    @else
                        <span style="background: #EAECEE; color: #7F8C8D; font-size: 0.75rem; font-weight: 700; padding: 6px 12px; border-radius: 6px;">
                            BẢN NHÁP (CHƯA DRY-RUN)
                        </span>
                    @endif
                </div>
            </div>

            {{-- Metrics Grid --}}
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-top: 18px; padding-top: 18px; border-top: 1px solid #ECE7E1;">
                <div>
                    <div style="font-size: 0.72rem; text-transform: uppercase; color: var(--mh-admin-muted); font-weight: 700;">Kênh Gửi</div>
                    <div style="font-size: 0.95rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-top: 4px;">{{ $dispatch->channel_label }}</div>
                </div>
                <div>
                    <div style="font-size: 0.72rem; text-transform: uppercase; color: var(--mh-admin-muted); font-weight: 700;">Tin Hợp Lệ</div>
                    <div style="font-size: 0.95rem; font-weight: 700; color: #27AE60; margin-top: 4px;">{{ $dispatch->successful_count ?: ($dispatch->total_recipients - $dispatch->deduplicated_count) }}</div>
                </div>
                <div>
                    <div style="font-size: 0.72rem; text-transform: uppercase; color: var(--mh-admin-muted); font-weight: 700;">Khử Trùng SĐT</div>
                    <div style="font-size: 0.95rem; font-weight: 700; color: #D35400; margin-top: 4px;">-{{ $dispatch->deduplicated_count }}</div>
                </div>
                <div>
                    <div style="font-size: 0.72rem; text-transform: uppercase; color: var(--mh-admin-muted); font-weight: 700;">Chi Phí Thực</div>
                    <div style="font-size: 0.95rem; font-weight: 700; color: #2980B9; margin-top: 4px;">{{ number_format($dispatch->total_cost_vnd, 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>

        {{-- Right: Dual Approval & Dispatch Actions Cockpit --}}
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 22px 26px; background: #FAF8F5;">
            <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0 0 12px;">
                Quy Trình Kiểm Soát Phát Tin
            </h3>

            {{-- Step 1: Dry-Run --}}
            <div style="margin-bottom: 16px;">
                <form action="{{ route('admin.messaging.dry_run', $dispatch->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="mayhen_admin_btn" style="width: 100%; padding: 10px 14px; font-size: 0.85rem; font-weight: 600; background: #FFFFFF; color: var(--mh-admin-charcoal); border: 1px solid #D1CCC6; border-radius: 8px; cursor: pointer;">
                        🔄 1. Chạy Kiểm Thử Dry-Run (Khử trùng SĐT)
                    </button>
                </form>
                <div style="font-size: 0.72rem; color: #7A756E; margin-top: 4px;">Không tốn phí &bull; Lập danh sách preview</div>
            </div>

            {{-- Step 2: Dual Approval & Real Dispatch --}}
            <div>
                @if($dispatch->status === 'dry_run_ready')
                    @if(Auth::user()?->isAdmin())
                        <form action="{{ route('admin.messaging.dispatch_real', $dispatch->id) }}" method="POST" onsubmit="return confirm('XÁC NHẬN PHÊ DUYỆT: Bạn có chắc chắn muốn phát tin nhắn thực tế tới quan khách? Chi phí dự tính: {{ number_format($dispatch->total_cost_vnd, 0, ',', '.') }}đ.');">
                            @csrf
                            <button type="submit" class="mayhen_admin_btn" style="width: 100%; padding: 12px 14px; font-size: 0.9rem; font-weight: 700; background: #27AE60; color: #FFFFFF; border: none; border-radius: 8px; cursor: pointer;">
                                🚀 2. Phê Duyệt & Phát Tin Thật
                            </button>
                        </form>
                        <div style="font-size: 0.72rem; color: #27AE60; margin-top: 4px; font-weight: 600;">Dual Approval: Yêu cầu Quản lý trở lên</div>
                    @else
                        <button disabled class="mayhen_admin_btn" style="width: 100%; padding: 10px 14px; font-size: 0.85rem; background: #ECE7E1; color: #999; border: none; border-radius: 8px;">
                            Chờ Quản Lý Phê Duyệt Phát Tin
                        </button>
                    @endif
                @elseif($dispatch->status === 'completed')
                    <div style="background: #E8F8F5; color: #117864; padding: 12px; border-radius: 8px; font-size: 0.82rem; font-weight: 600; text-align: center;">
                        Chiến dịch đã được duyệt bởi: <strong>{{ $dispatch->approver?->name ?: 'Quản trị viên' }}</strong>
                    </div>
                @else
                    <div style="font-size: 0.75rem; color: #7A756E; text-align: center; padding: 8px; background: #FFF; border-radius: 6px;">
                        Vui lòng bấm "Chạy Kiểm Thử Dry-Run" ở Bước 1 trước khi mở duyệt gửi thật.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Template Preview Card --}}
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 20px 24px;">
        <div style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 8px;">
            Mẫu Tin Nhắn Gốc Của Đợt Gửi
        </div>
        <div style="background: #FAF8F5; border: 1px solid #ECE7E1; border-radius: 8px; padding: 14px 18px; font-size: 0.85rem; color: #4A463F; white-space: pre-wrap; line-height: 1.5; font-family: monospace;">{{ $dispatch->message_template }}</div>
    </div>

    {{-- Recipients & Rendered Message Logs Table --}}
    <div class="mayhen_admin_card" style="padding: 0; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #ECE7E1; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                Danh Sách Khách Nhận Tin & Nội Dung Cá Nhân Hóa ({{ $dispatch->logs->count() }} khách)
            </h3>
            <span style="font-size: 0.75rem; color: #7A756E;">Hiển thị bản xem trước sau khi chạy Dry-Run</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #FAF8F5; border-bottom: 1px solid #ECE7E1;">
                        <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); width: 220px;">Khách Mời</th>
                        <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); width: 140px;">Số Điện Thoại</th>
                        <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted);">Nội Dung Cá Nhân Hóa (Preview)</th>
                        <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); width: 180px;">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dispatch->logs as $log)
                        <tr style="border-bottom: 1px solid #F0ECE6; background: {{ $log->status === 'skipped_duplicate' ? '#FFFDF9' : '#FFFFFF' }};">
                            <td style="padding: 14px 18px; vertical-align: top;">
                                <div style="font-weight: 700; color: var(--mh-admin-charcoal); font-size: 0.88rem;">
                                    {{ $log->guest_name }}
                                </div>
                                @if($log->guest_group)
                                    <div style="font-size: 0.75rem; color: #7A756E; margin-top: 2px;">
                                        Nhóm: {{ $log->guest_group }}
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 14px 18px; vertical-align: top; font-family: monospace; font-size: 0.85rem; color: var(--mh-admin-charcoal);">
                                {{ $log->phone }}
                            </td>
                            <td style="padding: 14px 18px; vertical-align: top;">
                                <div style="font-size: 0.82rem; color: #4A463F; background: #FAF8F5; padding: 10px 14px; border-radius: 6px; border: 1px solid #ECE7E1; line-height: 1.45; white-space: pre-wrap;">{{ $log->rendered_message }}</div>
                                @if($log->provider_ref)
                                    <div style="font-size: 0.72rem; color: #27AE60; font-family: monospace; margin-top: 4px;">
                                        Mã đối soát: {{ $log->provider_ref }} ({{ $log->sent_at?->format('H:i d/m/Y') }})
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 14px 18px; vertical-align: top;">
                                @if($log->status === 'sent')
                                    <span style="background: #E8F8F5; color: #117864; font-size: 0.72rem; font-weight: 700; padding: 4px 8px; border-radius: 4px; border: 1px solid #A3E4D7; display: inline-block;">
                                        ✓ ĐÃ GỬI THÀNH CÔNG
                                    </span>
                                @elseif($log->status === 'dry_run')
                                    <span style="background: #FEF9E7; color: #B7950B; font-size: 0.72rem; font-weight: 700; padding: 4px 8px; border-radius: 4px; border: 1px solid #F9E79F; display: inline-block;">
                                        DRY-RUN (SẴN SÀNG)
                                    </span>
                                @elseif($log->status === 'skipped_duplicate')
                                    <span style="background: #FDEDEC; color: #922B21; font-size: 0.72rem; font-weight: 700; padding: 4px 8px; border-radius: 4px; border: 1px solid #F5B7B1; display: inline-block;">
                                        BỎ QUA (TRÙNG SĐT)
                                    </span>
                                    <div style="font-size: 0.72rem; color: #922B21; margin-top: 4px; line-height: 1.3;">
                                        {{ $log->error_message }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding: 40px 20px; text-align: center; color: var(--mh-admin-muted);">
                                Chưa có nhật ký khách mời nào. Vui lòng bấm <strong>"Chạy Kiểm Thử Dry-Run"</strong> để khởi tạo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

