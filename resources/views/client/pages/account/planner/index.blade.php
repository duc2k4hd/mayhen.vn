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
            @elseif($activeTab === 'budget')
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
            @else
                @if($vendors->isEmpty())
                    <form action="{{ route('account.planner.vendors.seed_defaults', $invitation->id) }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="mayhen_account_btn mayhen_account_btn_outline">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                            <span>Khởi Tạo 8 Đối Tác Mẫu</span>
                        </button>
                    </form>
                @endif
                <a href="{{ route('account.planner.vendors.export', $invitation->id) }}" class="mayhen_account_btn mayhen_account_btn_outline">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    <span>Xuất CSV Danh Bạ</span>
                </a>
                <button type="button" class="mayhen_account_btn mayhen_account_btn_primary" onclick="openModal('modal-create-vendor')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Thêm Nhà Cung Cấp</span>
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
        <a href="{{ route('account.planner.index', ['id' => $invitation->id, 'tab' => 'vendors']) }}" 
           class="mayhen_planner_tab_btn {{ $activeTab === 'vendors' ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            <span>Nhà Cung Cấp &amp; Hợp Đồng</span>
            <span style="font-size: 11px; background: {{ $activeTab === 'vendors' ? '#9C7A4A' : '#EAE3D9' }}; color: {{ $activeTab === 'vendors' ? '#FFF' : '#7A726A' }}; padding: 1px 6px; border-radius: 10px;">
                {{ $summary['vendors']['total'] ?? 0 }}
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

    @elseif($activeTab === 'budget')
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
    @else
        {{-- TAB 3: NHÀ CUNG CẤP & HỢP ĐỒNG (VENDORS) --}}
        {{-- 4 KPI Summary Cards for Vendors --}}
        <div class="mayhen_planner_summary_grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 24px;">
            <div class="mayhen_planner_summary_card">
                <span class="mayhen_planner_summary_label">Tổng Nhà Cung Cấp</span>
                <span class="mayhen_planner_summary_val">{{ $summary['vendors']['total'] }} đơn vị</span>
                <span class="mayhen_planner_summary_desc">Đã ký kết: {{ $summary['vendors']['signed'] }} đối tác</span>
            </div>

            <div class="mayhen_planner_summary_card">
                <span class="mayhen_planner_summary_label">Tổng Giá Trị Hợp Đồng</span>
                <span class="mayhen_planner_summary_val">{{ number_format($summary['vendors']['total_contract']) }} đ</span>
                <span class="mayhen_planner_summary_desc">Cam kết dịch vụ cưới</span>
            </div>

            <div class="mayhen_planner_summary_card">
                <span class="mayhen_planner_summary_label">Đã Đặt Cọc</span>
                <span class="mayhen_planner_summary_val" style="color: #2E7D32;">{{ number_format($summary['vendors']['total_deposit']) }} đ</span>
                <span class="mayhen_planner_summary_desc">Tiền cọc giữ lịch</span>
            </div>

            <div class="mayhen_planner_summary_card">
                <span class="mayhen_planner_summary_label">Còn Phải Trả Ngày Cưới</span>
                <span class="mayhen_planner_summary_val" style="color: #C62828;">{{ number_format($summary['vendors']['total_balance_due']) }} đ</span>
                <span class="mayhen_planner_summary_desc">Công nợ cần tất toán</span>
            </div>
        </div>

        {{-- Vendors Table Card --}}
        <div class="mayhen_planner_table_card">
            @if($vendors->isEmpty())
                <div style="padding: 48px 24px; text-align: center;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#C5A880" stroke-width="1.5" style="margin-bottom: 16px;">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 24px; color: #1F1D1A; margin: 0 0 8px 0;">
                        Chưa có đối tác / nhà cung cấp nào
                    </h3>
                    <p style="font-size: 14px; color: #7A726A; max-width: 480px; margin: 0 auto 20px auto;">
                        Quản lý toàn bộ danh bạ đơn vị dịch vụ từ sảnh tiệc, quay phim, chụp ảnh, hoa cưới đến xe hoa. Theo dõi hợp đồng, tiền cọc và công nợ cần thanh toán trong ngày cưới.
                    </p>
                    <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                        <form action="{{ route('account.planner.vendors.seed_defaults', $invitation->id) }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="mayhen_account_btn mayhen_account_btn_outline">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                                <span>Khởi Tạo 8 Đối Tác Mẫu</span>
                            </button>
                        </form>
                        <button type="button" class="mayhen_account_btn mayhen_account_btn_primary" onclick="openModal('modal-create-vendor')">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <span>Thêm Nhà Cung Cấp Đầu Tiên</span>
                        </button>
                    </div>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table class="mayhen_planner_table">
                        <thead>
                            <tr>
                                <th>Nhà Cung Cấp &amp; Hạng Mục</th>
                                <th>Đầu Mối &amp; Hotline</th>
                                <th>Trạng Thái HĐ</th>
                                <th>Tổng Giá Trị</th>
                                <th>Đã Đặt Cọc</th>
                                <th>Còn Phải Trả</th>
                                <th>Lịch Trình Ngày Cưới</th>
                                <th style="text-align: right;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendors as $v)
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: #1F1D1A; font-size: 14px;">{{ $v->name }}</div>
                                        <span class="mayhen_planner_cat_badge" style="background: #FAF8F5; color: #9C7A4A; border: 1px solid #EAE3D9; margin-top: 4px; display: inline-block;">
                                            {{ $v->category_label }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($v->contact_person)
                                            <div style="font-weight: 500; color: #4A453E;">{{ $v->contact_person }}</div>
                                        @endif
                                        @if($v->phone)
                                            <div style="font-size: 12px; margin-top: 2px;">
                                                <a href="tel:{{ $v->phone }}" style="color: #9C7A4A; text-decoration: none; font-weight: 600;">📞 {{ $v->phone }}</a>
                                            </div>
                                        @else
                                            <span style="color: #A0988E; font-size: 12px;">Chưa có SĐT</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'contacting' => ['bg' => '#FFFBEB', 'text' => '#92400E'],
                                                'contract_signed' => ['bg' => '#EFF6FF', 'text' => '#1E40AF'],
                                                'in_progress' => ['bg' => '#FAF5FF', 'text' => '#6B21A8'],
                                                'completed' => ['bg' => '#ECFDF5', 'text' => '#065F46'],
                                                'cancelled' => ['bg' => '#FEF2F2', 'text' => '#991B1B'],
                                            ];
                                            $stCol = $statusColors[$v->contract_status] ?? ['bg' => '#F3F4F6', 'text' => '#374151'];
                                        @endphp
                                        <span class="mayhen_planner_badge" style="background: {{ $stCol['bg'] }}; color: {{ $stCol['text'] }}; font-weight: 600;">
                                            {{ $v->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong style="color: #1F1D1A;">{{ number_format($v->total_amount) }} đ</strong>
                                    </td>
                                    <td>
                                        <span style="color: #2E7D32; font-weight: 600;">{{ number_format($v->deposit_paid) }} đ</span>
                                    </td>
                                    <td>
                                        @if($v->balance_due > 0)
                                            <strong style="color: #C62828;">{{ number_format($v->balance_due) }} đ</strong>
                                        @else
                                            <span style="color: #2E7D32; font-size: 12px; font-weight: 600;">✓ Đã tất toán</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($v->service_timeline)
                                            <div style="font-size: 12px; color: #4A453E;">{{ $v->service_timeline }}</div>
                                        @endif
                                        @if($v->payment_due_date)
                                            <div style="font-size: 11px; color: #8C827A; margin-top: 2px;">
                                                Hạn TT: <strong>{{ $v->payment_due_date->format('d/m/Y') }}</strong>
                                            </div>
                                        @endif
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <button type="button" class="mayhen_planner_icon_btn"
                                                onclick="openDepositVendorModal({
                                                    id: {{ $v->id }},
                                                    name: @js($v->name),
                                                    total_amount: {{ $v->total_amount }},
                                                    deposit_paid: {{ $v->deposit_paid }},
                                                    balance_due: {{ $v->balance_due }},
                                                    deposit_url: @js(route('account.planner.vendors.deposit', ['id' => $invitation->id, 'vendorId' => $v->id]))
                                                })"
                                                title="Cập nhật tiền cọc">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"></rect><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                        </button>

                                        <button type="button" class="mayhen_planner_icon_btn"
                                                onclick="openEditVendorModal({
                                                    id: {{ $v->id }},
                                                    name: @js($v->name),
                                                    category: @js($v->category),
                                                    contact_person: @js($v->contact_person),
                                                    phone: @js($v->phone),
                                                    email: @js($v->email),
                                                    address: @js($v->address),
                                                    contract_status: @js($v->contract_status),
                                                    total_amount: {{ $v->total_amount }},
                                                    deposit_paid: {{ $v->deposit_paid }},
                                                    payment_due_date: @js($v->payment_due_date?->format('Y-m-d')),
                                                    service_timeline: @js($v->service_timeline),
                                                    notes: @js($v->notes),
                                                    update_url: @js(route('account.planner.vendors.update', ['id' => $invitation->id, 'vendorId' => $v->id]))
                                                })"
                                                title="Sửa thông tin đối tác">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                        </button>

                                        <form action="{{ route('account.planner.vendors.destroy', ['id' => $invitation->id, 'vendorId' => $v->id]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đối tác này?');" style="margin: 0; display: inline-block;">
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

{{-- Modal 6: Thêm đối tác / nhà cung cấp --}}
<div class="mayhen_planner_modal" id="modal-create-vendor">
    <div class="mayhen_planner_modal_content">
        <div class="mayhen_planner_modal_header">
            <h3 class="mayhen_planner_modal_title">Thêm Đối Tác / Nhà Cung Cấp</h3>
            <button type="button" class="mayhen_planner_icon_btn" onclick="closeModal('modal-create-vendor')">&times;</button>
        </div>
        <form action="{{ route('account.planner.vendors.store', $invitation->id) }}" method="POST">
            @csrf
            <div class="mayhen_planner_modal_body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Tên thương hiệu / Nhà cung cấp *</label>
                        <input type="text" name="name" required placeholder="Ví dụ: White Palace, TuArt Wedding..." style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Lĩnh vực / Hạng mục *</label>
                        <select name="category" style="width: 100%; padding: 10px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px;">
                            <option value="venue">Trung tâm tiệc cưới & Khách sạn</option>
                            <option value="catering">Tiệc bàn & Ẩm thực outside</option>
                            <option value="photography">Quay phim & Chụp ảnh phóng sự</option>
                            <option value="attire">Váy cưới, Vest & Áo dài</option>
                            <option value="makeup">Trang điểm & Làm tóc cô dâu</option>
                            <option value="decor">Trang trí gia tiên & Sảnh tiệc</option>
                            <option value="music_mc">MC, Ban nhạc & Vũ đoàn</option>
                            <option value="wedding_planner">Wedding Planner điều phối</option>
                            <option value="favors">Quà tặng cảm ơn & Thiệp</option>
                            <option value="transport">Xe hoa & Xe đưa đón</option>
                            <option value="other">Nhà cung cấp khác</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Người phụ trách / Liên hệ</label>
                        <input type="text" name="contact_person" placeholder="Anh Hưng (Quản lý), Chị Lan..." style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Số điện thoại liên hệ</label>
                        <input type="text" name="phone" placeholder="0901234567" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Email liên hệ</label>
                        <input type="email" name="email" placeholder="contact@vendor.com" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Trạng thái hợp đồng *</label>
                        <select name="contract_status" style="width: 100%; padding: 10px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px;">
                            <option value="contacting">Đang liên hệ / Báo giá</option>
                            <option value="contract_signed" selected>Đã ký hợp đồng</option>
                            <option value="in_progress">Đang thực hiện</option>
                            <option value="completed">Đã hoàn tất & Nghiệm thu</option>
                            <option value="cancelled">Hủy bỏ hợp đồng</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Tổng giá trị hợp đồng (VND) *</label>
                        <input type="number" name="total_amount" required min="0" value="0" step="100000" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Đã đặt cọc / Thanh toán (VND)</label>
                        <input type="number" name="deposit_paid" min="0" value="0" step="100000" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Hạn thanh toán đợt tiếp theo</label>
                        <input type="date" name="payment_due_date" style="width: 100%; padding: 10px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Thời gian phục vụ / Triển khai</label>
                        <input type="text" name="service_timeline" placeholder="Ví dụ: 08:00 - 13:00 Ngày cưới..." style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Địa chỉ làm việc / Sảnh tiệc / Studio</label>
                    <input type="text" name="address" placeholder="Địa chỉ chi nhánh hoặc vị trí phục vụ..." style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Ghi chú hợp đồng & Điều khoản cam kết</label>
                    <textarea name="notes" rows="2" placeholder="Cam kết bồi thường, điều khoản phát sinh..." style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;"></textarea>
                </div>
            </div>
            <div class="mayhen_planner_modal_footer">
                <button type="button" class="mayhen_account_btn mayhen_account_btn_outline" onclick="closeModal('modal-create-vendor')">Hủy</button>
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">Thêm Đối Tác</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 7: Chỉnh sửa đối tác / nhà cung cấp --}}
<div class="mayhen_planner_modal" id="modal-edit-vendor">
    <div class="mayhen_planner_modal_content">
        <div class="mayhen_planner_modal_header">
            <h3 class="mayhen_planner_modal_title">Chỉnh Sửa Thông Tin Đối Tác</h3>
            <button type="button" class="mayhen_planner_icon_btn" onclick="closeModal('modal-edit-vendor')">&times;</button>
        </div>
        <form id="form-edit-vendor" method="POST">
            @csrf
            @method('PUT')
            <div class="mayhen_planner_modal_body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Tên thương hiệu / Nhà cung cấp *</label>
                        <input type="text" id="edit-vendor-name" name="name" required style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Lĩnh vực / Hạng mục *</label>
                        <select id="edit-vendor-category" name="category" style="width: 100%; padding: 10px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px;">
                            <option value="venue">Trung tâm tiệc cưới & Khách sạn</option>
                            <option value="catering">Tiệc bàn & Ẩm thực outside</option>
                            <option value="photography">Quay phim & Chụp ảnh phóng sự</option>
                            <option value="attire">Váy cưới, Vest & Áo dài</option>
                            <option value="makeup">Trang điểm & Làm tóc cô dâu</option>
                            <option value="decor">Trang trí gia tiên & Sảnh tiệc</option>
                            <option value="music_mc">MC, Ban nhạc & Vũ đoàn</option>
                            <option value="wedding_planner">Wedding Planner điều phối</option>
                            <option value="favors">Quà tặng cảm ơn & Thiệp</option>
                            <option value="transport">Xe hoa & Xe đưa đón</option>
                            <option value="other">Nhà cung cấp khác</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Người phụ trách / Liên hệ</label>
                        <input type="text" id="edit-vendor-contact-person" name="contact_person" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Số điện thoại</label>
                        <input type="text" id="edit-vendor-phone" name="phone" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Email</label>
                        <input type="email" id="edit-vendor-email" name="email" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Trạng thái hợp đồng *</label>
                        <select id="edit-vendor-status" name="contract_status" style="width: 100%; padding: 10px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px;">
                            <option value="contacting">Đang liên hệ / Báo giá</option>
                            <option value="contract_signed">Đã ký hợp đồng</option>
                            <option value="in_progress">Đang thực hiện</option>
                            <option value="completed">Đã hoàn tất & Nghiệm thu</option>
                            <option value="cancelled">Hủy bỏ hợp đồng</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Tổng giá trị hợp đồng (VND) *</label>
                        <input type="number" id="edit-vendor-total" name="total_amount" required min="0" step="100000" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Đã đặt cọc / Thanh toán (VND)</label>
                        <input type="number" id="edit-vendor-deposit" name="deposit_paid" min="0" step="100000" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Hạn thanh toán đợt tiếp theo</label>
                        <input type="date" id="edit-vendor-due-date" name="payment_due_date" style="width: 100%; padding: 10px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Thời gian phục vụ / Triển khai</label>
                        <input type="text" id="edit-vendor-timeline" name="service_timeline" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Địa chỉ làm việc / Sảnh tiệc / Studio</label>
                    <input type="text" id="edit-vendor-address" name="address" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Ghi chú hợp đồng & Điều khoản</label>
                    <textarea id="edit-vendor-notes" name="notes" rows="2" style="width: 100%; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; box-sizing: border-box;"></textarea>
                </div>
            </div>
            <div class="mayhen_planner_modal_footer">
                <button type="button" class="mayhen_account_btn mayhen_account_btn_outline" onclick="closeModal('modal-edit-vendor')">Hủy</button>
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 8: Cập nhật nhanh số tiền cọc đối tác --}}
<div class="mayhen_planner_modal" id="modal-deposit-vendor">
    <div class="mayhen_planner_modal_content">
        <div class="mayhen_planner_modal_header">
            <h3 class="mayhen_planner_modal_title">Cập Nhật Thanh Toán / Đặt Cọc</h3>
            <button type="button" class="mayhen_planner_icon_btn" onclick="closeModal('modal-deposit-vendor')">&times;</button>
        </div>
        <form id="form-deposit-vendor" method="POST">
            @csrf
            <div class="mayhen_planner_modal_body">
                <div style="background: #F9F6F0; padding: 14px 16px; border-radius: 8px; margin-bottom: 16px;">
                    <div style="font-weight: 600; color: #1F1D1A; font-size: 14px;" id="deposit-vendor-title">Nhà cung cấp</div>
                    <div style="font-size: 13px; color: #7A726A; margin-top: 4px;">
                        Tổng hợp đồng: <strong id="deposit-vendor-total" style="color: #1F1D1A;">0 đ</strong>
                        | Còn thiếu: <strong id="deposit-vendor-balance" style="color: #C62828;">0 đ</strong>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Số tiền đã thanh toán lũy kế (VND) *</label>
                    <input type="number" id="deposit-vendor-amount" name="deposit_paid" required min="0" step="100000" style="width: 100%; padding: 12px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 16px; font-weight: 700; color: #2E7D32; box-sizing: border-box;">
                    <div style="font-size: 12px; color: #8C827A; margin-top: 4px;">
                        Nhập số tiền đã thanh toán tích lũy đến hiện tại.
                    </div>
                </div>
            </div>
            <div class="mayhen_planner_modal_footer">
                <button type="button" class="mayhen_account_btn mayhen_account_btn_outline" onclick="closeModal('modal-deposit-vendor')">Hủy</button>
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">Cập Nhật Tiền Cọc</button>
            </div>
        </form>
    </div>
</div>
@endsection

