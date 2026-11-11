@extends('admin.layouts.master')

@section('title', 'Hộp Thư Tác Vụ Vận Hành')
@section('page_title', 'Hộp Thư Tác Vụ Vận Hành Tập Trung (Action Inbox)')

@section('content')
    {{-- KPI Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Tổng Việc Cần Giải Quyết
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                {{ number_format($kpis['total'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Được tổng hợp từ toàn bộ phân hệ</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #C0392B;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                P0 — Cực Kỳ Khẩn Cấp
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #C0392B;">
                {{ number_format($kpis['p0'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #C0392B; margin-top: 4px; font-weight: 600;">Đơn hàng chờ thanh toán</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #E67E22;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                P1 — Mức Độ Cao
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #E67E22;">
                {{ number_format($kpis['p1'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #E67E22; margin-top: 4px; font-weight: 600;">Ticket khẩn, Radar rủi ro, Lời chúc</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #2980B9;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                P2 — Bình Thường
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #2980B9;">
                {{ number_format($kpis['p2'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #2980B9; margin-top: 4px; font-weight: 600;">Tác vụ nền, kiểm tra định kỳ</div>
        </div>
    </div>

    {{-- Bộ Lọc Mức Độ Ưu Tiên --}}
    <div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
        <a href="{{ route('admin.intelligence.inbox') }}"
           class="mayhen_admin_btn {{ $currentPriority === 'all' ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn--ghost' }}"
           style="padding: 8px 16px; font-size: 0.85rem;">
            Tất cả ({{ $kpis['total'] }})
        </a>
        <a href="{{ route('admin.intelligence.inbox', ['priority' => 'p0']) }}"
           class="mayhen_admin_btn {{ $currentPriority === 'p0' ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn--ghost' }}"
           style="padding: 8px 16px; font-size: 0.85rem; color: {{ $currentPriority === 'p0' ? '#fff' : '#C0392B' }};">
            🔴 P0 — Khẩn ({{ $kpis['p0'] }})
        </a>
        <a href="{{ route('admin.intelligence.inbox', ['priority' => 'p1']) }}"
           class="mayhen_admin_btn {{ $currentPriority === 'p1' ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn--ghost' }}"
           style="padding: 8px 16px; font-size: 0.85rem; color: {{ $currentPriority === 'p1' ? '#fff' : '#E67E22' }};">
            🟠 P1 — Cao ({{ $kpis['p1'] }})
        </a>
        <a href="{{ route('admin.intelligence.inbox', ['priority' => 'p2']) }}"
           class="mayhen_admin_btn {{ $currentPriority === 'p2' ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn--ghost' }}"
           style="padding: 8px 16px; font-size: 0.85rem; color: {{ $currentPriority === 'p2' ? '#fff' : '#2980B9' }};">
            🔵 P2 — Thường ({{ $kpis['p2'] }})
        </a>
    </div>

    {{-- Danh Sách Tác Vụ --}}
    @if($actions->isEmpty())
        <div class="mayhen_admin_card" style="padding: 56px 20px; text-align: center; color: var(--mh-admin-muted);">
            <div style="font-size: 2.5rem; margin-bottom: 12px;">☕</div>
            <div style="font-weight: 700; font-size: 1.1rem; color: var(--mh-admin-charcoal);">Hộp thư tác vụ trống! Mọi việc đã hoàn tất</div>
            <div style="font-size: 0.875rem; margin-top: 6px;">Không có vấn đề P0, P1 hay P2 nào tồn đọng trong hệ thống lúc này.</div>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @foreach($actions as $action)
                @php
                    $borderLeftColor = match($action['priority']) {
                        'p0' => '#C0392B',
                        'p1' => '#E67E22',
                        'p2' => '#2980B9',
                        default => '#7A756E',
                    };
                    $badgeClass = match($action['priority']) {
                        'p0' => 'mayhen_admin_badge--danger',
                        'p1' => 'mayhen_admin_badge--warning',
                        'p2' => 'mayhen_admin_badge--info',
                        default => 'mayhen_admin_badge--neutral',
                    };
                @endphp
                <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 16px 20px; border-left: 4px solid {{ $borderLeftColor }}; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                    <div style="flex: 1; min-width: 280px;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                            <span class="mayhen_admin_badge {{ $badgeClass }}">{{ $action['priority_label'] }}</span>
                            <span style="font-size: 0.75rem; color: var(--mh-admin-muted); font-weight: 600; text-transform: uppercase;">{{ $action['category'] }}</span>
                            <span style="font-size: 0.75rem; color: #999;">&bull; {{ $action['created_at']->diffForHumans() }}</span>
                        </div>
                        <div style="font-weight: 700; font-size: 0.95rem; color: var(--mh-admin-charcoal); margin-bottom: 4px;">
                            {{ $action['title'] }}
                        </div>
                        <div style="font-size: 0.85rem; color: #666; line-height: 1.5;">
                            {{ $action['description'] }}
                        </div>
                    </div>

                    <div>
                        <a href="{{ $action['action_url'] }}"
                           class="mayhen_admin_btn {{ $action['priority'] === 'p0' ? 'mayhen_admin_btn--primary' : 'mayhen_admin_btn--secondary' }}"
                           style="padding: 8px 18px; font-size: 0.85rem; white-space: nowrap;">
                            {{ $action['action_label'] }} &rarr;
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

