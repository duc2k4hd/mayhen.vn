@extends('admin.layouts.master')

@section('title', 'Delight Recovery Planner')
@section('page_title', '💡 Delight Recovery Planner (Hỗ Trợ Cặp Đôi Gặp Khó Khăn)')

@section('content')
    <!-- KPI Header Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Tổng Ca Cần Hỗ Trợ
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                {{ $kpis['total'] }}
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Cặp đôi cần CSKH trợ giúp</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #C62828;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #C62828; margin-bottom: 6px;">
                Đã Trả Tiền Chưa Hoàn Tất
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #C62828;">
                {{ $kpis['paid_unpublished'] }}
            </div>
            <div style="font-size: 0.75rem; color: #C62828; margin-top: 4px; font-weight: 600;">Mức độ ưu tiên cao</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #E65100;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #E65100; margin-bottom: 6px;">
                Bản Nháp Bị Kẹt (> 3 ngày)
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #E65100;">
                {{ $kpis['stuck_drafts'] }}
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Chưa hoàn tất nội dung</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #0D47A1;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #0D47A1; margin-bottom: 6px;">
                Xem Nhiều Nhưng 0 RSVP
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #0D47A1;">
                {{ $kpis['zero_rsvps'] }}
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Cần kiểm tra kỹ thuật form</div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header">
            <div class="mayhen_admin_card_title">
                <span>📋 Danh Sách Cặp Đôi Cần Chủ Động Chăm Sóc (Delight Outreach Queue)</span>
            </div>
        </div>

        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th>Cặp Đôi & Khách Hàng</th>
                        <th>Vấn Đề Phát Hiện</th>
                        <th>Thời Gian Hoạt Động</th>
                        <th>Mức Độ</th>
                        <th>Gợi Ý CSKH & Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cases as $case)
                        <tr>
                            <td>
                                <strong>{{ $case['user']->name ?? 'Khách Hàng' }}</strong><br>
                                <span style="font-size: 0.75rem; color: var(--mh-admin-muted);">
                                    Email: {{ $case['user']->email ?? '—' }} &bull; SĐT: <strong>{{ $case['user']->phone ?? 'Chưa có' }}</strong>
                                </span>
                                @if(isset($case['wedding']))
                                    <div style="font-size: 0.75rem; color: #8C827A; margin-top: 2px;">
                                        Đám cưới: {{ $case['wedding']->groom_name ?? '—' }} &amp; {{ $case['wedding']->bride_name ?? '—' }}
                                    </div>
                                @endif
                            </td>
                            <td style="max-width: 280px;">
                                <strong style="font-size: 0.8125rem; color: var(--mh-admin-charcoal);">{{ $case['title'] }}</strong>
                                <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 2px;">
                                    {{ $case['reason'] }}
                                </div>
                            </td>
                            <td style="font-size: 0.8125rem; white-space: nowrap;">
                                {{ $case['last_activity'] }}
                            </td>
                            <td>
                                @if($case['severity'] === 'high')
                                    <span class="mayhen_admin_badge mayhen_admin_badge_danger">Khẩn Cấp</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge_warning">Cần Lưu Ý</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                    @if(isset($case['user']->id))
                                        <a href="{{ route('admin.users.show', $case['user']->id) }}" class="mayhen_admin_btn mayhen_admin_btn--primary mayhen_admin_btn_sm">
                                            Hồ Sơ 360 &rarr;
                                        </a>
                                    @endif
                                    @if(isset($case['invitation']->id))
                                        <a href="{{ route('admin.invitations.show', $case['invitation']->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                                            Xem Thiệp &rarr;
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--mh-admin-muted);">
                                <div style="font-size: 2rem; margin-bottom: 8px;">🎉</div>
                                <strong>Tuyệt vời! Không có cặp đôi nào đang gặp khó khăn.</strong><br>
                                <span style="font-size: 0.8125rem;">Toàn bộ khách hàng đều đang tạo thiệp và nhận phản hồi thuận lợi.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
