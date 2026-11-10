@extends('client.pages.account.layout')

@section('page_title', 'Kế Hoạch & Sổ Dự Toán Ngân Sách Cưới | ' . config('app.name'))
@section('account_breadcrumb', 'Kế Hoạch & Ngân Sách Cưới')

@section('additional_head')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/planner.css') }}">
    <script src="{{ asset('assets/client/js/pages/planner.js') }}" defer></script>
@endsection

@section('account_content')
<div class="mayhen_planner_container">
    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <div style="margin-bottom: 8px;">
                <a href="{{ route('account.invitations') }}" style="color: #8C827A; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                    &larr; <span>Quay lại danh sách thiệp</span>
                </a>
            </div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; color: #1F1D1A; margin: 0;">
                Kế Hoạch & Sổ Dự Toán Ngân Sách Cưới
            </h2>
            <p style="font-size: 14px; color: #8C827A; margin: 4px 0 0 0;">
                Đám cưới: <strong style="color: #1F1D1A;">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</strong> • 
                @if($wedding->wedding_date)
                    Ngày cưới: <strong style="color: #9C7A4A;">{{ $wedding->wedding_date->format('d/m/Y') }}</strong>
                @else
                    <span style="color: #A0988E;">Chưa ấn định ngày cưới</span>
                @endif
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @if($activeTab === 'checklist')
                @if($tasks->isEmpty())
                    <form action="{{ route('account.planner.seed_defaults', $invitation->id) }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="mayhen_account_btn mayhen_account_btn_outline">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                            <span>Khởi Tạo Checklist Chuẩn (16 Việc)</span>
                        </button>
                    </form>
                @endif
                <button type="button" class="mayhen_account_btn mayhen_account_btn_primary" onclick="openModal('modal-create-task')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Thêm Việc Cần Làm</span>
                </button>
            @else
                <button type="button" class="mayhen_account_btn mayhen_account_btn_outline" onclick="openModal('modal-target-budget')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                    <span>Hạn Mức Ngân Sách</span>
                </button>
                <a href="{{ route('account.planner.budget.export', $invitation->id) }}" class="mayhen_account_btn mayhen_account_btn_outline">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    <span>Xuất CSV Kế Toán</span>
                </a>
                <button type="button" class="mayhen_account_btn mayhen_account_btn_primary" onclick="openModal('modal-create-budget')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Thêm Khoản Chi</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div style="background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tabs Navigation --}}
    <div class="mayhen_planner_tabs_nav">
        <a href="{{ route('account.planner.index', ['id' => $invitation->id, 'tab' => 'checklist']) }}" 
           class="mayhen_planner_tab_btn {{ $activeTab === 'checklist' ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
            <span>Danh Mục Việc Cần Làm (Checklist)</span>
            <span style="font-size: 11px; background: {{ $activeTab === 'checklist' ? '#9C7A4A' : '#EAE3D9' }}; color: {{ $activeTab === 'checklist' ? '#FFF' : '#7A726A' }}; padding: 1px 6px; border-radius: 10px;">
                {{ $summary['tasks']['total'] }}
            </span>
        </a>
        <a href="{{ route('account.planner.index', ['id' => $invitation->id, 'tab' => 'budget']) }}" 
           class="mayhen_planner_tab_btn {{ $activeTab === 'budget' ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            <span>Dự Toán Ngân Sách (Budget Planner)</span>
            <span style="font-size: 11px; background: {{ $activeTab === 'budget' ? '#9C7A4A' : '#EAE3D9' }}; color: {{ $activeTab === 'budget' ? '#FFF' : '#7A726A' }}; padding: 1px 6px; border-radius: 10px;">
                {{ number_format($summary['budget']['total_actual'] ?: $summary['budget']['total_estimated']) }} đ
            </span>
        </a>
    </div>

    @if($activeTab === 'checklist')
        {{-- ==================== TAB 1: CHECKLIST ==================== --}}
        {{-- KPI Summary Cards --}}
        <div class="mayhen_planner_kpi_grid">
            <div class="mayhen_planner_kpi_card">
                <span class="mayhen_planner_kpi_label">Tổng Số Công Việc</span>
                <span class="mayhen_planner_kpi_value">{{ $summary['tasks']['total'] }}</span>
                <span class="mayhen_planner_kpi_desc">Việc cần chuẩn bị trước và sau cưới</span>
            </div>
            <div class="mayhen_planner_kpi_card">
                <span class="mayhen_planner_kpi_label">Đã Hoàn Thành</span>
                <span class="mayhen_planner_kpi_value" id="planner-completed-kpi" style="color: #047857;">{{ $summary['tasks']['completed'] }}</span>
                <span class="mayhen_planner_kpi_desc">Đã tick xong trong danh sách</span>
            </div>
            <div class="mayhen_planner_kpi_card">
                <span class="mayhen_planner_kpi_label">Đang Chờ Thực Hiện</span>
                <span class="mayhen_planner_kpi_value" style="color: #9C7A4A;">{{ $summary['tasks']['pending'] + $summary['tasks']['in_progress'] }}</span>
                <span class="mayhen_planner_kpi_desc">Việc cần theo dõi và giải quyết</span>
            </div>
            <div class="mayhen_planner_kpi_card">
                <span class="mayhen_planner_kpi_label">Quá Hạn (Cần Gấp)</span>
                <span class="mayhen_planner_kpi_value" style="color: {{ $summary['tasks']['overdue'] > 0 ? '#B91C1C' : '#7A726A' }};">
                    {{ $summary['tasks']['overdue'] }}
                </span>
                <span class="mayhen_planner_kpi_desc">Đã quá ngày đến hạn dự kiến</span>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="mayhen_planner_progress_wrapper">
            <div class="mayhen_planner_progress_header">
                <span style="font-weight: 600; color: #1F1D1A;">Tiến Độ Chuẩn Bị Ngày Cưới:</span>
                <strong id="planner-progress-text" style="color: #9C7A4A;">
                    {{ $summary['tasks']['completed'] }}/{{ $summary['tasks']['total'] }} việc ({{ $summary['tasks']['progress_percent'] }}%)
                </strong>
            </div>
            <div class="mayhen_planner_progress_bar">
                <div class="mayhen_planner_progress_fill" id="planner-progress-fill" style="width: {{ $summary['tasks']['progress_percent'] }}%;"></div>
            </div>
        </div>

        {{-- Task Lists by Phase --}}
        @php
            $phaseLabels = [
                '12_months' => 'Trước 9 - 12 tháng (Khởi đầu & Đặt chỗ)',
                '6_months' => 'Trước 4 - 6 tháng (Chụp ảnh & Trang phục)',
                '3_months' => 'Trước 2 - 3 tháng (Thiệp Mây Hẹn & Mâm quả)',
                '1_month' => 'Trước 1 tháng (Gửi thiệp & Hậu cần)',
                '1_week' => 'Tuần lễ cưới (Chốt bàn & Diễn tập)',
                'wedding_day' => 'Ngày diễn ra hôn lễ (Lễ & Tiệc)',
                'post_wedding' => 'Hậu sự kiện & Lời cảm ơn',
            ];
        @endphp

        @if($tasks->isEmpty())
            <div style="background: #FFFFFF; border: 1px dashed #D8CEBD; border-radius: 14px; padding: 48px 24px; text-align: center;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#C5A880" stroke-width="1.5" style="margin-bottom: 16px;">
                    <path d="M9 11l3 3L22 4"></path>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 24px; color: #1F1D1A; margin: 0 0 8px 0;">
                    Chưa có công việc nào trong kế hoạch cưới
                </h3>
                <p style="font-size: 14px; color: #7A726A; max-width: 460px; margin: 0 auto 20px auto;">
                    Bạn có thể tự tạo từng đầu việc hoặc bấm nút bên dưới để Mây Hẹn tự động tạo sẵn bộ checklist 16 việc thiết yếu chuẩn mực văn hóa cưới hỏi Việt Nam.
                </p>
                <form action="{{ route('account.planner.seed_defaults', $invitation->id) }}" method="POST" style="display: inline-block;">
                    @csrf
                    <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                        <span>Khởi Tạo Ngay Bộ Checklist 16 Việc Chuẩn</span>
                    </button>
                </form>
            </div>
        @else
            @foreach($tasksByPhase as $phaseKey => $phaseTasks)
                @if($phaseTasks->isNotEmpty())
                    <div class="mayhen_planner_phase_card">
                        <div class="mayhen_planner_phase_header">
                            <span class="mayhen_planner_phase_title">
                                <span>{{ $phaseLabels[$phaseKey] ?? $phaseKey }}</span>
                                <span class="mayhen_planner_phase_count">
                                    {{ $phaseTasks->where('status', 'completed')->count() }}/{{ $phaseTasks->count() }} việc
                                </span>
                            </span>
                        </div>
                        <div class="mayhen_planner_task_list">
                            @foreach($phaseTasks as $task)
                                <div class="mayhen_planner_task_row {{ $task->isCompleted() ? 'is_completed' : '' }}">
                                    {{-- Checkbox --}}
                                    <div class="mayhen_planner_checkbox {{ $task->isCompleted() ? 'checked' : '' }}" 
                                         data-toggle-url="{{ route('account.planner.tasks.toggle', ['id' => $invitation->id, 'taskId' => $task->id]) }}"
                                         title="Bấm để đánh dấu hoàn thành">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    </div>

                                    {{-- Task Info --}}
                                    <div>
                                        <div class="mayhen_planner_task_title">{{ $task->title }}</div>
                                        @if($task->description)
                                            <div style="font-size: 13px; color: #7A726A; margin-bottom: 4px;">{{ $task->description }}</div>
                                        @endif
                                        <div class="mayhen_planner_task_meta">
                                            <span class="mayhen_planner_badge mayhen_planner_badge_{{ $task->priority }}">
                                                {{ $task->priority_label }}
                                            </span>
                                            @if($task->assignee_name)
                                                <span>👤 Phụ trách: <strong>{{ $task->assignee_name }}</strong></span>
                                            @endif
                                            @if($task->due_date)
                                                <span>📅 Hạn chót: {{ $task->due_date->format('d/m/Y') }}</span>
                                            @endif
                                            @if($task->isOverdue())
                                                <span class="mayhen_planner_badge mayhen_planner_badge_overdue">⚠️ Quá hạn</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="mayhen_planner_task_actions" style="display: flex; gap: 6px;">
                                        <button type="button" class="mayhen_planner_icon_btn" 
                                                onclick="openEditTaskModal({
                                                    id: {{ $task->id }},
                                                    title: @js($task->title),
                                                    phase: @js($task->phase),
                                                    priority: @js($task->priority),
                                                    assignee_name: @js($task->assignee_name),
                                                    due_date: @js($task->due_date ? $task->due_date->format('Y-m-d') : ''),
                                                    description: @js($task->description),
                                                    update_url: @js(route('account.planner.tasks.update', ['id' => $invitation->id, 'taskId' => $task->id]))
                                                })" 
                                                title="Chỉnh sửa công việc">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                        </button>

                                        <form action="{{ route('account.planner.tasks.destroy', ['id' => $invitation->id, 'taskId' => $task->id]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa công việc này?');" style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="mayhen_planner_icon_btn mayhen_planner_icon_btn_del" title="Xóa">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        @endif

    @else
        {{-- ==================== TAB 2: BUDGET PLANNER ==================== --}}
        {{-- KPI Budget Grid --}}
        <div class="mayhen_planner_kpi_grid">
            <div class="mayhen_planner_kpi_card">
                <span class="mayhen_planner_kpi_label">Hạn Mức Ngân Sách</span>
                <span class="mayhen_planner_kpi_value" style="color: #9C7A4A;">
                    {{ number_format($summary['budget']['target_budget']) }} đ
                </span>
                <span class="mayhen_planner_kpi_desc">Mục tiêu tài chính dự trù</span>
            </div>
            <div class="mayhen_planner_kpi_card">
                <span class="mayhen_planner_kpi_label">Tổng Dự Toán</span>
                <span class="mayhen_planner_kpi_value">{{ number_format($summary['budget']['total_estimated']) }} đ</span>
                <span class="mayhen_planner_kpi_desc">Ước tính các khoản chi</span>
            </div>
            <div class="mayhen_planner_kpi_card">
                <span class="mayhen_planner_kpi_label">Tổng Thực Chi</span>
                <span class="mayhen_planner_kpi_value" style="color: #1F1D1A;">{{ number_format($summary['budget']['total_actual']) }} đ</span>
                <span class="mayhen_planner_kpi_desc">Hóa đơn thực tế theo hợp đồng</span>
            </div>
            <div class="mayhen_planner_kpi_card">
                <span class="mayhen_planner_kpi_label">Đã Cọc / Đã Trả</span>
                <span class="mayhen_planner_kpi_value" style="color: #047857;">{{ number_format($summary['budget']['total_paid']) }} đ</span>
                <span class="mayhen_planner_kpi_desc">Còn thiếu: {{ number_format($summary['budget']['total_pending']) }} đ</span>
            </div>
        </div>

        {{-- Category Breakdown Progress Bar --}}
        @if($summary['budget']['effective_spent'] > 0)
            <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 14px; padding: 20px; margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span style="font-weight: 600; color: #1F1D1A; font-size: 14px;">Cơ Cấu Chi Phí Theo Hạng Mục:</span>
                    <span style="font-size: 13px; color: #7A726A;">
                        Tổng chi: <strong>{{ number_format($summary['budget']['effective_spent']) }} đ</strong>
                        @if($summary['budget']['target_budget'] > 0)
                            • Còn lại: <strong style="color: {{ $summary['budget']['remaining_from_target'] >= 0 ? '#047857' : '#B91C1C' }};">
                                {{ number_format($summary['budget']['remaining_from_target']) }} đ
                            </strong>
                        @endif
                    </span>
                </div>

                {{-- Color segments --}}
                @php
                    $catColors = [
                        'venue_catering' => '#9C7A4A',
                        'attire_jewelry' => '#C5A880',
                        'photography_video' => '#4A5568',
                        'invitations_favors' => '#D9B4A7',
                        'decor_flowers' => '#9AA899',
                        'ceremony_offerings' => '#E53E3E',
                        'entertainment_music' => '#3182CE',
                        'contingency' => '#D69E2E',
                        'other' => '#CBD5E0',
                    ];
                @endphp
                <div class="mayhen_planner_cat_bar">
                    @foreach($summary['budget']['category_breakdown'] as $catKey => $catData)
                        @if($catData['percent'] > 0)
                            <div class="mayhen_planner_cat_segment" 
                                 style="width: {{ $catData['percent'] }}%; background: {{ $catColors[$catKey] ?? '#A0AEC0' }};"
                                 title="{{ $catData['name'] }}: {{ number_format($catData['cost']) }} đ ({{ $catData['percent'] }}%)"></div>
                        @endif
                    @endforeach
                </div>

                {{-- Legend Badges --}}
                <div style="display: flex; flex-wrap: wrap; gap: 12px; font-size: 12px;">
                    @foreach($summary['budget']['category_breakdown'] as $catKey => $catData)
                        @if($catData['count'] > 0)
                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: {{ $catColors[$catKey] ?? '#A0AEC0' }};"></span>
                                <span style="color: #1F1D1A;">{{ $catData['name'] }}:</span>
                                <strong style="color: #7A726A;">{{ number_format($catData['cost']) }} đ ({{ $catData['percent'] }}%)</strong>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Budget Items Table --}}
        <div class="mayhen_planner_table_card">
            @if($budgetItems->isEmpty())
                <div style="padding: 48px 24px; text-align: center;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#C5A880" stroke-width="1.5" style="margin-bottom: 16px;">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                    <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 24px; color: #1F1D1A; margin: 0 0 8px 0;">
                        Chưa có khoản mục chi phí nào
                    </h3>
                    <p style="font-size: 14px; color: #7A726A; max-width: 440px; margin: 0 auto 20px auto;">
                        Lập danh sách các khoản dự toán từ sảnh tiệc, trang phục, thiệp cưới đến chụp ảnh để dễ dàng quản lý dòng tiền và tránh phát sinh ngoài ý muốn.
                    </p>
                    <button type="button" class="mayhen_account_btn mayhen_account_btn_primary" onclick="openModal('modal-create-budget')">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Thêm Khoản Chi Đầu Tiên</span>
                    </button>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table class="mayhen_planner_table">
                        <thead>
                            <tr>
                                <th>Khoản Chi & Hạng Mục</th>
                                <th>Dự Toán</th>
                                <th>Thực Chi</th>
                                <th>Đã Cọc / Trả</th>
                                <th>Còn Thiếu</th>
                                <th>Chênh Lệch</th>
                                <th>Nhà Cung Cấp</th>
                                <th>Trạng Thái</th>
                                <th style="text-align: right;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($budgetItems as $item)
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: #1F1D1A;">{{ $item->item_name }}</div>
                                        <div style="font-size: 11px; color: #8C827A;">{{ $item->category_label }}</div>
                                        @if($item->notes)
                                            <div style="font-size: 11px; color: #6B635B; margin-top: 2px; font-style: italic;">
                                                "{{ Str::limit($item->notes, 40) }}"
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ number_format($item->estimated_cost) }} đ</td>
                                    <td>
                                        @if($item->actual_cost > 0)
                                            <strong style="color: #1F1D1A;">{{ number_format($item->actual_cost) }} đ</strong>
                                        @else
                                            <span style="color: #A0988E;">--</span>
                                        @endif
                                    </td>
                                    <td style="color: #047857; font-weight: 600;">
                                        {{ number_format($item->paid_amount) }} đ
                                    </td>
                                    <td style="color: {{ $item->pending_amount > 0 ? '#B91C1C' : '#7A726A' }}; font-weight: 600;">
                                        {{ number_format($item->pending_amount) }} đ
                                    </td>
                                    <td>
                                        @if($item->variance > 0)
                                            <span style="color: #B91C1C; font-weight: 600; font-size: 12px;">+{{ number_format($item->variance) }} đ</span>
                                        @elseif($item->variance < 0)
                                            <span style="color: #047857; font-weight: 600; font-size: 12px;">{{ number_format($item->variance) }} đ</span>
                                        @else
                                            <span style="color: #A0988E; font-size: 12px;">0 đ</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->vendor_name)
                                            <div>{{ $item->vendor_name }}</div>
                                            @if($item->vendor_contact)
                                                <div style="font-size: 11px; color: #8C827A;">{{ $item->vendor_contact }}</div>
                                            @endif
                                        @else
                                            <span style="color: #A0988E;">Chưa có</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->is_paid_in_full)
                                            <span class="mayhen_planner_badge" style="background: #DEF7EC; color: #03543F; border: 1px solid #BCF0DA;">
                                                ✓ Đã tất toán
                                            </span>
                                        @elseif($item->paid_amount > 0)
                                            <span class="mayhen_planner_badge" style="background: #FEF3C7; color: #D97706; border: 1px solid #FDE68A;">
                                                Đã cọc một phần
                                            </span>
                                        @else
                                            <span class="mayhen_planner_badge" style="background: #FAF8F5; color: #7A726A; border: 1px solid #EAE3D9;">
                                                Chưa thanh toán
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <button type="button" class="mayhen_planner_icon_btn"
                                                onclick="openEditBudgetModal({
                                                    id: {{ $item->id }},
                                                    item_name: @js($item->item_name),
                                                    category: @js($item->category),
                                                    estimated_cost: {{ $item->estimated_cost }},
                                                    actual_cost: {{ $item->actual_cost }},
                                                    paid_amount: {{ $item->paid_amount }},
                                                    vendor_name: @js($item->vendor_name),
                                                    vendor_contact: @js($item->vendor_contact),
                                                    notes: @js($item->notes),
                                                    update_url: @js(route('account.planner.budget.update', ['id' => $invitation->id, 'itemId' => $item->id]))
                                                })"
                                                title="Sửa khoản chi">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                        </button>

                                        <form action="{{ route('account.planner.budget.destroy', ['id' => $invitation->id, 'itemId' => $item->id]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khoản chi này?');" style="margin: 0; display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="mayhen_planner_icon_btn mayhen_planner_icon_btn_del" title="Xóa">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</div>

{{-- ==================== MODALS ==================== --}}

{{-- Modal 1: Thêm việc cần làm --}}
<div class="mayhen_planner_modal" id="modal-create-task">
    <div class="mayhen_planner_modal_content">
        <div class="mayhen_planner_modal_header">
            <h3 class="mayhen_planner_modal_title">Thêm Việc Cần Làm</h3>
            <button type="button" class="mayhen_planner_icon_btn" onclick="closeModal('modal-create-task')">&times;</button>
        </div>
        <form action="{{ route('account.planner.tasks.store', $invitation->id) }}" method="POST">
            @csrf
            <div class="mayhen_planner_modal_body">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Tiêu đề công việc *</label>
                    <input type="text" name="title" required placeholder="Ví dụ: Đặt cọc sảnh tiệc, Thử áo dài..." style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Giai đoạn chuẩn bị *</label>
                        <select name="phase" style="width: 100%; padding: 10px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px;">
                            <option value="12_months">Trước 9 - 12 tháng</option>
                            <option value="6_months">Trước 4 - 6 tháng</option>
                            <option value="3_months">Trước 2 - 3 tháng</option>
                            <option value="1_month">Trước 1 tháng</option>
                            <option value="1_week">Tuần lễ cưới</option>
                            <option value="wedding_day" selected>Ngày diễn ra hôn lễ</option>
                            <option value="post_wedding">Hậu sự kiện & Cảm ơn</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Mức độ ưu tiên *</label>
                        <select name="priority" style="width: 100%; padding: 10px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px;">
                            <option value="urgent">Khẩn cấp</option>
                            <option value="high">Ưu tiên cao</option>
                            <option value="medium" selected>Bình thường</option>
                            <option value="low">Thấp</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Người phụ trách</label>
                        <input type="text" name="assignee_name" placeholder="Chú rể, Cô dâu, Bạn thân..." style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Hạn chót (Due date)</label>
                        <input type="date" name="due_date" style="width: 100%; padding: 10px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Ghi chú chi tiết</label>
                    <textarea name="description" rows="3" placeholder="Chi tiết yêu cầu, lưu ý..." style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;"></textarea>
                </div>
            </div>
            <div class="mayhen_planner_modal_footer">
                <button type="button" class="mayhen_account_btn mayhen_account_btn_outline" onclick="closeModal('modal-create-task')">Hủy</button>
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">Thêm Công Việc</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 2: Sửa việc cần làm --}}
<div class="mayhen_planner_modal" id="modal-edit-task">
    <div class="mayhen_planner_modal_content">
        <div class="mayhen_planner_modal_header">
            <h3 class="mayhen_planner_modal_title">Chỉnh Sửa Công Việc</h3>
            <button type="button" class="mayhen_planner_icon_btn" onclick="closeModal('modal-edit-task')">&times;</button>
        </div>
        <form id="form-edit-task" method="POST">
            @csrf
            @method('PUT')
            <div class="mayhen_planner_modal_body">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Tiêu đề công việc *</label>
                    <input type="text" id="edit-task-title" name="title" required style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Giai đoạn chuẩn bị *</label>
                        <select id="edit-task-phase" name="phase" style="width: 100%; padding: 10px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px;">
                            <option value="12_months">Trước 9 - 12 tháng</option>
                            <option value="6_months">Trước 4 - 6 tháng</option>
                            <option value="3_months">Trước 2 - 3 tháng</option>
                            <option value="1_month">Trước 1 tháng</option>
                            <option value="1_week">Tuần lễ cưới</option>
                            <option value="wedding_day">Ngày diễn ra hôn lễ</option>
                            <option value="post_wedding">Hậu sự kiện & Cảm ơn</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Mức độ ưu tiên *</label>
                        <select id="edit-task-priority" name="priority" style="width: 100%; padding: 10px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px;">
                            <option value="urgent">Khẩn cấp</option>
                            <option value="high">Ưu tiên cao</option>
                            <option value="medium">Bình thường</option>
                            <option value="low">Thấp</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Người phụ trách</label>
                        <input type="text" id="edit-task-assignee" name="assignee_name" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Hạn chót</label>
                        <input type="date" id="edit-task-due-date" name="due_date" style="width: 100%; padding: 10px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Ghi chú chi tiết</label>
                    <textarea id="edit-task-desc" name="description" rows="3" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;"></textarea>
                </div>
            </div>
            <div class="mayhen_planner_modal_footer">
                <button type="button" class="mayhen_account_btn mayhen_account_btn_outline" onclick="closeModal('modal-edit-task')">Hủy</button>
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 3: Thêm khoản chi --}}
<div class="mayhen_planner_modal" id="modal-create-budget">
    <div class="mayhen_planner_modal_content">
        <div class="mayhen_planner_modal_header">
            <h3 class="mayhen_planner_modal_title">Thêm Khoản Chi Mới</h3>
            <button type="button" class="mayhen_planner_icon_btn" onclick="closeModal('modal-create-budget')">&times;</button>
        </div>
        <form action="{{ route('account.planner.budget.store', $invitation->id) }}" method="POST">
            @csrf
            <div class="mayhen_planner_modal_body">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Tên khoản chi *</label>
                    <input type="text" name="item_name" required placeholder="Ví dụ: Đặt cọc sảnh tiệc, Thuê váy cưới..." style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Hạng mục chi phí *</label>
                    <select name="category" style="width: 100%; padding: 10px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px;">
                        <option value="venue_catering">Sảnh Tiệc & Ẩm Thực</option>
                        <option value="attire_jewelry">Trang Phục, Nhẫn & Trang Sức</option>
                        <option value="photography_video">Chụp Ảnh & Phóng Sự Cưới</option>
                        <option value="invitations_favors">Thiệp Cưới & Quà Đáp Lễ</option>
                        <option value="decor_flowers">Trang Trí Hoa Tươi & Gia Tiên</option>
                        <option value="ceremony_offerings">Mâm Quả, Tráp Cưới & Xe Hoa</option>
                        <option value="entertainment_music">Âm Nhạc, MC & Ban Nhạc</option>
                        <option value="contingency">Chi Phí Dự Phòng Phát Sinh</option>
                        <option value="other">Hạng mục khác</option>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Chi phí dự toán (VND) *</label>
                        <input type="number" name="estimated_cost" required min="0" value="0" step="50000" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Thực chi (VND)</label>
                        <input type="number" name="actual_cost" min="0" value="0" step="50000" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Đã thanh toán / Đặt cọc (VND)</label>
                        <input type="number" name="paid_amount" min="0" value="0" step="50000" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Tên nhà cung cấp</label>
                        <input type="text" name="vendor_name" placeholder="Studio, Nhà hàng..." style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Thông tin liên hệ nhà cung cấp</label>
                    <input type="text" name="vendor_contact" placeholder="SĐT, Địa chỉ, Link liên hệ..." style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Ghi chú chi tiết</label>
                    <textarea name="notes" rows="2" placeholder="Hạn thanh toán đợt 2, điều khoản hợp đồng..." style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;"></textarea>
                </div>
            </div>
            <div class="mayhen_planner_modal_footer">
                <button type="button" class="mayhen_account_btn mayhen_account_btn_outline" onclick="closeModal('modal-create-budget')">Hủy</button>
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">Thêm Khoản Chi</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 4: Sửa khoản chi --}}
<div class="mayhen_planner_modal" id="modal-edit-budget">
    <div class="mayhen_planner_modal_content">
        <div class="mayhen_planner_modal_header">
            <h3 class="mayhen_planner_modal_title">Chỉnh Sửa Khoản Chi</h3>
            <button type="button" class="mayhen_planner_icon_btn" onclick="closeModal('modal-edit-budget')">&times;</button>
        </div>
        <form id="form-edit-budget" method="POST">
            @csrf
            @method('PUT')
            <div class="mayhen_planner_modal_body">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Tên khoản chi *</label>
                    <input type="text" id="edit-budget-name" name="item_name" required style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Hạng mục chi phí *</label>
                    <select id="edit-budget-cat" name="category" style="width: 100%; padding: 10px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px;">
                        <option value="venue_catering">Sảnh Tiệc & Ẩm Thực</option>
                        <option value="attire_jewelry">Trang Phục, Nhẫn & Trang Sức</option>
                        <option value="photography_video">Chụp Ảnh & Phóng Sự Cưới</option>
                        <option value="invitations_favors">Thiệp Cưới & Quà Đáp Lễ</option>
                        <option value="decor_flowers">Trang Trí Hoa Tươi & Gia Tiên</option>
                        <option value="ceremony_offerings">Mâm Quả, Tráp Cưới & Xe Hoa</option>
                        <option value="entertainment_music">Âm Nhạc, MC & Ban Nhạc</option>
                        <option value="contingency">Chi Phí Dự Phòng Phát Sinh</option>
                        <option value="other">Hạng mục khác</option>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Chi phí dự toán (VND) *</label>
                        <input type="number" id="edit-budget-est" name="estimated_cost" required min="0" step="50000" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Thực chi (VND)</label>
                        <input type="number" id="edit-budget-act" name="actual_cost" min="0" step="50000" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Đã thanh toán (VND)</label>
                        <input type="number" id="edit-budget-paid" name="paid_amount" min="0" step="50000" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Tên nhà cung cấp</label>
                        <input type="text" id="edit-budget-vendor" name="vendor_name" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Liên hệ nhà cung cấp</label>
                    <input type="text" id="edit-budget-contact" name="vendor_contact" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Ghi chú</label>
                    <textarea id="edit-budget-notes" name="notes" rows="2" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;"></textarea>
                </div>
            </div>
            <div class="mayhen_planner_modal_footer">
                <button type="button" class="mayhen_account_btn mayhen_account_btn_outline" onclick="closeModal('modal-edit-budget')">Hủy</button>
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 5: Đặt hạn mức ngân sách mục tiêu --}}
<div class="mayhen_planner_modal" id="modal-target-budget">
    <div class="mayhen_planner_modal_content">
        <div class="mayhen_planner_modal_header">
            <h3 class="mayhen_planner_modal_title">Hạn Mức Ngân Sách Mục Tiêu</h3>
            <button type="button" class="mayhen_planner_icon_btn" onclick="closeModal('modal-target-budget')">&times;</button>
        </div>
        <form action="{{ route('account.planner.budget.target', $invitation->id) }}" method="POST">
            @csrf
            <div class="mayhen_planner_modal_body">
                <p style="font-size: 13px; color: #7A726A; margin: 0 0 16px 0;">
                    Đặt ra số tiền dự kiến tối đa cho toàn bộ hôn lễ giúp cô dâu chú rể kiểm soát dòng tiền thông minh, tránh chi tiêu vượt khả năng.
                </p>
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Tổng ngân sách mục tiêu (VND) *</label>
                    <input type="number" name="target_budget" required min="0" step="500000" value="{{ $summary['budget']['target_budget'] }}" style="width: 100%; padding: 12px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 16px; font-weight: 700; color: #9C7A4A; box-sizing: border-box;">
                </div>
            </div>
            <div class="mayhen_planner_modal_footer">
                <button type="button" class="mayhen_account_btn mayhen_account_btn_outline" onclick="closeModal('modal-target-budget')">Hủy</button>
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">Lưu Hạn Mức</button>
            </div>
        </form>
    </div>
</div>
@endsection

