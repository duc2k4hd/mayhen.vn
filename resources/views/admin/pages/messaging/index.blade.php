@extends('admin.layouts.master')

@section('title', 'Phát Tin Thiệp Mời Tin Cậy (Reliable Messaging Dispatch)')
@section('page_title', 'Phát Tin Thiệp Mời Tin Cậy')

@section('content')
    {{-- KPI Overview Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Tổng Chiến Dịch
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                {{ number_format($kpis['total_campaigns'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Đợt gửi tin thiệp mời</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #27AE60;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Tin Đã Gửi Thành Công
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #27AE60;">
                {{ number_format($kpis['total_sent'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #27AE60; margin-top: 4px; font-weight: 600;">Đã chuyển đến khách mời</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #2980B9;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Tổng Chi Phí Viễn Thông
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #2980B9;">
                {{ number_format($kpis['total_cost_vnd'], 0, ',', '.') }} đ
            </div>
            <div style="font-size: 0.75rem; color: #2980B9; margin-top: 4px; font-weight: 600;">Zalo ZNS & SMS Brandname</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #D35400; background: #FFF8F0;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #D35400; margin-bottom: 6px;">
                🛡️ Tin Tiết Kiệm (Chống Trùng)
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #D35400;">
                {{ number_format($kpis['deduplicated_saved'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #D35400; margin-top: 4px; font-weight: 600;">Khử trùng số điện thoại</div>
        </div>
    </div>

    {{-- Actions & Filter Bar --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <form method="GET" action="{{ route('admin.messaging.index') }}" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <select name="channel" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 8px; border: 1px solid #D1CCC6; font-size: 0.85rem; background: #FFF;">
                <option value="">Tất cả kênh phát tin</option>
                <option value="zalo_zns" {{ request('channel') === 'zalo_zns' ? 'selected' : '' }}>Zalo ZNS</option>
                <option value="sms_brandname" {{ request('channel') === 'sms_brandname' ? 'selected' : '' }}>SMS Brandname</option>
                <option value="invitation_link" {{ request('channel') === 'invitation_link' ? 'selected' : '' }}>Link Thiệp Định Danh</option>
            </select>

            <select name="status" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 8px; border: 1px solid #D1CCC6; font-size: 0.85rem; background: #FFF;">
                <option value="">Tất cả trạng thái</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Bản nháp</option>
                <option value="dry_run_ready" {{ request('status') === 'dry_run_ready' ? 'selected' : '' }}>Đã Dry-Run (Sẵn sàng)</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Đã hoàn tất</option>
            </select>
        </form>

        <div>
            <a href="{{ route('admin.messaging.create') }}" class="mayhen_admin_btn" style="background: var(--mh-admin-gold); color: #FFFFFF; font-weight: 600; padding: 10px 18px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <span>+ Tạo Đợt Phát Tin Mới</span>
            </a>
        </div>
    </div>

    {{-- Dispatches Table --}}
    <div class="mayhen_admin_card" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #FAF8F5; border-bottom: 1px solid #ECE7E1;">
                        <th style="padding: 14px 18px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted);">Chiến Dịch</th>
                        <th style="padding: 14px 18px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted);">Tiệc Cưới</th>
                        <th style="padding: 14px 18px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted);">Kênh Gửi</th>
                        <th style="padding: 14px 18px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted);">Quy Mô</th>
                        <th style="padding: 14px 18px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted);">Chi Phí</th>
                        <th style="padding: 14px 18px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted);">Trạng Thái</th>
                        <th style="padding: 14px 18px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dispatches as $dispatch)
                        <tr style="border-bottom: 1px solid #F0ECE6;">
                            <td style="padding: 16px 18px;">
                                <div style="font-weight: 700; color: var(--mh-admin-charcoal); font-size: 0.9rem;">
                                    {{ $dispatch->campaign_name }}
                                </div>
                                <div style="font-size: 0.75rem; color: #7A756E; margin-top: 2px;">
                                    Tạo bởi: {{ $dispatch->creator?->name ?: 'Hệ thống' }} &bull; {{ $dispatch->created_at->format('H:i d/m/Y') }}
                                </div>
                            </td>
                            <td style="padding: 16px 18px;">
                                @if($dispatch->wedding)
                                    <div style="font-weight: 600; color: var(--mh-admin-charcoal); font-size: 0.88rem;">
                                        {{ $dispatch->wedding->groom_name }} & {{ $dispatch->wedding->bride_name }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: #7A756E;">
                                        Ngày cưới: {{ $dispatch->wedding->wedding_date ? \Carbon\Carbon::parse($dispatch->wedding->wedding_date)->format('d/m/Y') : 'N/A' }}
                                    </div>
                                @else
                                    <span style="color: #999;">—</span>
                                @endif
                            </td>
                            <td style="padding: 16px 18px;">
                                <span style="display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background: #F4F1EA; color: #4A463F;">
                                    {{ $dispatch->channel_label }}
                                </span>
                            </td>
                            <td style="padding: 16px 18px;">
                                <div style="font-size: 0.85rem; font-weight: 600; color: var(--mh-admin-charcoal);">
                                    {{ $dispatch->successful_count ?: $dispatch->total_recipients }} người nhận
                                </div>
                                @if($dispatch->deduplicated_count > 0)
                                    <div style="font-size: 0.72rem; color: #D35400;">
                                        Khử trùng: -{{ $dispatch->deduplicated_count }} SĐT
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 16px 18px;">
                                <div style="font-weight: 700; color: #2980B9; font-size: 0.88rem;">
                                    {{ number_format($dispatch->total_cost_vnd, 0, ',', '.') }} đ
                                </div>
                                <div style="font-size: 0.72rem; color: #7A756E;">
                                    Đơn giá: {{ number_format($dispatch->unit_cost_vnd, 0, ',', '.') }} đ/tin
                                </div>
                            </td>
                            <td style="padding: 16px 18px;">
                                @if($dispatch->status === 'completed')
                                    <span style="background: #E8F8F5; color: #117864; font-size: 0.72rem; font-weight: 700; padding: 4px 8px; border-radius: 4px; border: 1px solid #A3E4D7;">
                                        ✓ ĐÃ HOÀN TẤT
                                    </span>
                                @elseif($dispatch->status === 'dry_run_ready')
                                    <span style="background: #FEF9E7; color: #B7950B; font-size: 0.72rem; font-weight: 700; padding: 4px 8px; border-radius: 4px; border: 1px solid #F9E79F;">
                                        ĐÃ DRY-RUN (CHỜ DUYỆT)
                                    </span>
                                @elseif($dispatch->status === 'processing')
                                    <span style="background: #EBF5FB; color: #2980B9; font-size: 0.72rem; font-weight: 700; padding: 4px 8px; border-radius: 4px;">
                                        ĐANG GỬI TIN...
                                    </span>
                                @else
                                    <span style="background: #EAECEE; color: #7F8C8D; font-size: 0.72rem; font-weight: 700; padding: 4px 8px; border-radius: 4px;">
                                        BẢN NHÁP
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 16px 18px; text-align: right;">
                                <a href="{{ route('admin.messaging.show', $dispatch->id) }}" class="mayhen_admin_btn" style="padding: 6px 14px; font-size: 0.8rem; font-weight: 600; background: #FAF8F5; color: var(--mh-admin-charcoal); border: 1px solid #D1CCC6; border-radius: 6px; text-decoration: none;">
                                    Chi Tiết &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 40px 20px; text-align: center; color: var(--mh-admin-muted);">
                                Chưa có đợt phát tin nào trong hệ thống.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dispatches->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #ECE7E1;">
                {{ $dispatches->links() }}
            </div>
        @endif
    </div>
@endsection

