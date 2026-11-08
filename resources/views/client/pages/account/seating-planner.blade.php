@extends('client.layouts.master')

@section('title', 'Sơ Đồ Xếp Bàn Tiệc Studio: ' . $wedding->groom_name . ' & ' . $wedding->bride_name . ' | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/seating-planner.css') }}">
@endpush

@section('content')
<div class="mayhen_seat_wrap" id="mayhen_seating_app" 
    data-assign-url="{{ route('seating.assignGuest', $invitation->id) }}"
    data-positions-url="{{ route('seating.updatePositions', $invitation->id) }}"
    data-csrf="{{ csrf_token() }}"
>
    <div class="mayhen_seat_container">
        {{-- Breadcrumbs --}}
        <div class="mayhen_seat_breadcrumbs">
            <a href="{{ route('account.dashboard') }}">Tài khoản</a>
            <span>/</span>
            <a href="{{ route('account.invitations') }}">Thiệp cưới</a>
            <span>/</span>
            <a href="{{ route('guests.index', $invitation->id) }}">Khách mời</a>
            <span>/</span>
            <span>Sơ Đồ Xếp Bàn Tiệc</span>
        </div>

        @if(session('success'))
            <div class="mayhen_seat_alert mayhen_seat_alert--success">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mayhen_seat_alert mayhen_seat_alert--error">
                {{ session('error') }}
            </div>
        @endif

        {{-- Header Section --}}
        <header class="mayhen_seat_header">
            <div>
                <span class="seat_badge">SEATING PLANNER STUDIO</span>
                <h1 class="seat_title">Sơ Đồ Xếp Chỗ & Bàn Tiệc Cưới</h1>
                <p class="seat_subtitle">
                    Hôn lễ: <strong>{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</strong> &bull;
                    Trực quan hóa sơ đồ sảnh tiệc, kéo thả khách mời vào bàn và xuất sơ đồ đón tiếp tại cửa sảnh.
                </p>
            </div>
            <div class="seat_header_actions">
                <button type="button" class="mayhen_seat_btn mayhen_seat_btn--gold" id="btn_open_add_table">
                    + Thêm Bàn Tiệc Mới
                </button>
                <a href="{{ route('seating.print', $invitation->id) }}" target="_blank" class="mayhen_seat_btn mayhen_seat_btn--outline">
                    🖨️ In Sơ Đồ Sảnh Tiệc (PDF)
                </a>
                <a href="{{ route('guests.index', $invitation->id) }}" class="mayhen_seat_btn mayhen_seat_btn--outline">
                    👥 Danh Sách Khách
                </a>
            </div>
        </header>

        {{-- 4 Metric Stat Cards --}}
        <div class="mayhen_seat_stats_grid">
            <div class="mayhen_seat_stat_card">
                <span class="stat_label">TỔNG SỐ BÀN TIỆC</span>
                <div class="stat_value">{{ number_format($totalTablesCount) }}</div>
                <span class="stat_hint">Bàn trong sảnh tiệc</span>
            </div>

            <div class="mayhen_seat_stat_card">
                <span class="stat_label">SỨC CHỨA SẢNH TIỆC</span>
                <div class="stat_value">{{ number_format($totalCapacity) }}</div>
                <span class="stat_hint">Tổng số ghế ngồi dự kiến</span>
            </div>

            <div class="mayhen_seat_stat_card">
                <span class="stat_label">ĐÃ CÓ CHỖ NGỒI</span>
                <div class="stat_value" style="color: #2E7D32;">{{ number_format($assignedSeats) }}</div>
                <span class="stat_hint">Khách đã xếp vào bàn</span>
            </div>

            <div class="mayhen_seat_stat_card">
                <span class="stat_label">CHƯA CÓ BÀN TIỆC</span>
                <div class="stat_value" style="color: {{ $unassignedSeats > 0 ? '#E65100' : '#8C8479' }};">
                    {{ number_format($unassignedSeats) }}
                </div>
                <span class="stat_hint">Cần kéo thả vào bàn</span>
            </div>
        </div>

        {{-- TASK-VAL-22: Bác Sĩ Cảnh Báo Quá Tải & Dự Báo Đặt Bàn Tiệc (Seating Capacity Guardian) --}}
        @if(isset($seatingHealth))
            <div class="mayhen_seat_guardian_card">
                <div class="guardian_card_head">
                    <div class="guardian_title_wrap">
                        <span class="guardian_shield_icon">🛡️</span>
                        <div>
                            <h2 class="guardian_title">Bác Sĩ Bàn Tiệc & Dự Báo Sức Chứa (Seating Capacity Guardian)</h2>
                            <p class="guardian_subtitle">Tự động phát hiện bàn quá tải, bàn quá thưa, xung đột thế hệ và tính toán số bàn tiệc đặt nhà hàng</p>
                        </div>
                    </div>
                    <div class="guardian_score_group">
                        <span class="guardian_score_badge guardian_score_badge--{{ $seatingHealth['status'] }}">
                            <span class="guardian_score_num">{{ $seatingHealth['health_score'] }}</span>
                            <span class="guardian_score_max">/100 Điểm</span>
                        </span>
                        <strong style="font-size: 0.88rem; color: #1F1D1A;">{{ $seatingHealth['status_label'] }}</strong>
                    </div>
                </div>

                <div class="guardian_grid_summary">
                    <div class="guardian_metric_col">
                        <span class="guardian_metric_label">DỰ BÁO BÀN CHÍNH THỨC</span>
                        <div class="guardian_metric_val">{{ $seatingHealth['metrics']['recommended_official_tables'] }} Bàn</div>
                        <span class="guardian_metric_note">Tính chuẩn 10 khách/bàn cho toàn bộ khách tham dự</span>
                    </div>

                    <div class="guardian_metric_col">
                        <span class="guardian_metric_label">BÀN DỰ PHÒNG AN TOÀN (10%)</span>
                        <div class="guardian_metric_val" style="color: #7E57C2;">+{{ $seatingHealth['metrics']['recommended_buffer_tables'] }} Bàn</div>
                        <span class="guardian_metric_note">Dự phòng khách phát sinh và khách đổi bàn phút chót</span>
                    </div>

                    <div class="guardian_metric_col">
                        <span class="guardian_metric_label">TỶ LỆ LẤP ĐẦY SẢNH TIỆC</span>
                        <div class="guardian_metric_val" style="color: {{ $seatingHealth['metrics']['overall_occupancy_percent'] > 100 ? '#C62828' : '#2E7D32' }};">
                            {{ $seatingHealth['metrics']['overall_occupancy_percent'] }}%
                        </div>
                        <span class="guardian_metric_note">{{ $seatingHealth['metrics']['total_assigned'] }}/{{ $seatingHealth['metrics']['total_capacity'] }} ghế đã có chủ</span>
                    </div>

                    <div class="guardian_metric_col">
                        <span class="guardian_metric_label">TRẠNG THÁI PHÂN BỔ</span>
                        <div class="guardian_metric_val" style="font-size: 0.95rem;">
                            @if($seatingHealth['metrics']['overfilled_tables_count'] > 0)
                                <span style="color: #C62828;">⚠️ {{ $seatingHealth['metrics']['overfilled_tables_count'] }} bàn quá tải</span>
                            @else
                                <span style="color: #2E7D32;">✓ 0 bàn quá tải</span>
                            @endif
                            @if($seatingHealth['metrics']['underfilled_tables_count'] > 0)
                                &bull; <span style="color: #F57F17;">{{ $seatingHealth['metrics']['underfilled_tables_count'] }} bàn thưa</span>
                            @endif
                        </div>
                        <span class="guardian_metric_note">Khách chưa xếp bàn: {{ $seatingHealth['metrics']['total_unassigned'] }} người</span>
                    </div>
                </div>

                @if(!empty($seatingHealth['issues']))
                    <div class="guardian_issues_box">
                        <div class="guardian_issues_title">Cảnh Báo Cần Lưu Ý Từ Chuyên Gia Bàn Tiệc ({{ count($seatingHealth['issues']) }} vấn đề):</div>
                        <ul class="guardian_issue_list">
                            @foreach($seatingHealth['issues'] as $issue)
                                <li class="guardian_issue_item guardian_issue_item--{{ $issue['severity'] }}">
                                    <span>
                                        @if($issue['severity'] === 'critical') 🚨 @elseif($issue['severity'] === 'warning') ⚠️ @else 💡 @endif
                                    </span>
                                    <div>
                                        <strong>{{ $issue['message'] }}</strong>
                                        <span class="guardian_issue_suggest">Gợi ý: {{ $issue['suggestion'] }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        {{-- Main Two-Column Interactive Workspace --}}
        <div class="mayhen_seat_workspace">
            {{-- Left Area: Canvas Sơ Đồ Sảnh Tiệc --}}
            <div class="mayhen_seat_floorplan_wrap">
                <div class="floorplan_toolbar">
                    <div style="font-size: 0.88rem; font-weight: 600; color: #1F1D1A;">
                        📍 Kéo thả bàn để đổi vị trí &bull; Kéo khách từ cột phải thả vào bàn
                    </div>
                    @if($overCapacityCount > 0)
                        <span style="font-size: 0.78rem; font-weight: 700; color: #D32F2F; background: #FFEBEE; padding: 0.2rem 0.6rem; border-radius: 999px;">
                            ⚠️ Có {{ $overCapacityCount }} bàn vượt quá số ghế!
                        </span>
                    @endif
                </div>

                {{-- Sân Khấu Sảnh Tiệc --}}
                <div class="floorplan_stage_bar">
                    ✦ SÂN KHẤU TRUNG TÂM (STAGE & BACKDROP) ✦
                </div>

                {{-- The Floor Plan Canvas Surface --}}
                <div class="floorplan_canvas" id="floorplan_canvas">
                    @forelse($tables as $table)
                        @php
                            $isFull = $table->isFull();
                            $isOver = $table->isOverCapacity();
                            $assignedCount = $table->assigned_attendees_count;
                        @endphp
                        <div 
                            class="seat_table_item {{ $table->shape === 'round' ? 'seat_table_item--round' : '' }} {{ $isOver ? 'is-overcapacity' : '' }}"
                            id="table_{{ $table->id }}"
                            data-table-id="{{ $table->id }}"
                            data-pos-x="{{ $table->pos_x }}"
                            data-pos-y="{{ $table->pos_y }}"
                            style="left: {{ $table->pos_x }}px; top: {{ $table->pos_y }}px;"
                        >
                            <div class="table_head">
                                <span class="table_name" title="{{ $table->name }}">{{ $table->name }}</span>
                                <span class="table_capacity_pill {{ $isOver ? 'is-overflow' : ($isFull ? 'is-full' : '') }}">
                                    {{ $assignedCount }}/{{ $table->capacity }}
                                </span>
                            </div>

                            @if($isOver)
                                <div style="margin-bottom: 0.35rem;">
                                    <span class="table_warning_badge table_warning_badge--overfill">⚠️ Quá tải +{{ $assignedCount - $table->capacity }} người</span>
                                </div>
                            @elseif($assignedCount > 0 && $assignedCount < ($table->capacity * 0.5))
                                <div style="margin-bottom: 0.35rem;">
                                    <span class="table_warning_badge table_warning_badge--underfill">📉 Quá thưa ({{ $assignedCount }}/{{ $table->capacity }})</span>
                                </div>
                            @endif

                            {{-- Chips Khách Trong Bàn --}}
                            <div class="table_assigned_list">
                                @forelse($table->guests as $g)
                                    <span class="table_guest_chip" title="Bấm để gỡ khách" onclick="window.removeGuestFromTable({{ $g->id }})">
                                        {{ $g->name }} ({{ $g->expected_attendees ?: 1 }}) ✕
                                    </span>
                                @empty
                                    <span style="font-style: italic; color: #A39B8F;">(Bàn trống)</span>
                                @endforelse
                            </div>

                            <div class="table_foot_actions">
                                <span style="font-size: 0.68rem; color: #8C8479;">{{ $table->shape_label }}</span>
                                <form action="{{ route('seating.deleteTable', ['id' => $invitation->id, 'tableId' => $table->id]) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa bàn này? Khách trong bàn sẽ chuyển về chưa xếp bàn.');" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table_action_link" style="background: none; border: none; color: #C62828; padding: 0;">
                                        Xóa ✕
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 6rem 1rem; color: #8C8479;">
                            <div style="font-size: 3rem; margin-bottom: 0.5rem;">🍽️</div>
                            <strong style="display: block; font-size: 1.15rem; color: #1F1D1A; margin-bottom: 0.35rem;">Sảnh Tiệc Chưa Có Bàn Nào</strong>
                            <p style="font-size: 0.88rem; margin-bottom: 1.25rem;">Bấm nút "+ Thêm Bàn Tiệc Mới" để bắt đầu bố trí sơ đồ sảnh cưới.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Right Area: Ngăn Kéo Khách Mời Chưa Xếp Bàn --}}
            <aside class="mayhen_seat_drawer">
                <div class="drawer_head">
                    <h2 class="drawer_title">Khách Chưa Xếp Bàn</h2>
                    <span class="drawer_count_badge">{{ $unassignedGuests->count() }} khách</span>
                </div>

                <div class="drawer_search">
                    <input type="text" id="drawer_search_input" class="drawer_search_input" placeholder="🔍 Tìm tên hoặc nhóm khách...">
                </div>

                <div class="drawer_guests_list" id="drawer_guests_list">
                    @forelse($unassignedGuests as $ug)
                        <div 
                            class="guest_drag_card" 
                            draggable="true"
                            data-guest-id="{{ $ug->id }}"
                            data-guest-name="{{ $ug->name }}"
                            data-guest-group="{{ $ug->group_name }}"
                        >
                            <div class="guest_info_left">
                                <strong>{{ $ug->name }}</strong>
                                <span>{{ $ug->group_name ?: 'Khách chung' }}</span>
                            </div>
                            <span class="guest_attendees_badge">
                                {{ $ug->expected_attendees ?: 1 }} người
                            </span>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 2rem 0.5rem; color: #8C8479; font-size: 0.85rem;">
                            🎉 Tuyệt vời! Toàn bộ khách mời đã được xếp chỗ vào bàn tiệc.
                        </div>
                    @endforelse
                </div>
            </aside>
        </div>
    </div>

    {{-- Modal Thêm Bàn Tiệc Mới --}}
    <div class="mayhen_seat_modal_overlay" id="modal_add_table" style="display: none;">
        <div class="mayhen_seat_modal_box">
            <div class="modal_head">
                <h3 class="modal_title">Thêm Bàn Tiệc Mới</h3>
                <button type="button" class="modal_close_btn" id="btn_close_add_table">✕</button>
            </div>
            <form action="{{ route('seating.storeTable', $invitation->id) }}" method="POST">
                @csrf
                <div class="form_group" style="margin-bottom: 1rem;">
                    <label class="form_label" for="table_name">Tên Bàn Tiệc *</label>
                    <input type="text" name="name" id="table_name" class="form_input" required placeholder="Ví dụ: Bàn VIP 01, Bàn Bạn Đại Học..." maxlength="100">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form_group">
                        <label class="form_label" for="table_shape">Kiểu Dáng Bàn</label>
                        <select name="shape" id="table_shape" class="form_select">
                            <option value="round">Bàn Tròn (10 người)</option>
                            <option value="long">Bàn Dài (12-16 người)</option>
                            <option value="square">Bàn Vuông / VIP</option>
                        </select>
                    </div>

                    <div class="form_group">
                        <label class="form_label" for="table_capacity">Số Ghế Ngồi *</label>
                        <input type="number" name="capacity" id="table_capacity" class="form_input" required min="1" max="50" value="10">
                    </div>
                </div>

                <div class="form_group" style="margin-bottom: 1rem;">
                    <label class="form_label" for="table_zone">Khu Vực (Tùy chọn)</label>
                    <input type="text" name="zone" id="table_zone" class="form_input" placeholder="Ví dụ: Cánh Trái, Gần Sân Khấu, Tầng Lửng..." maxlength="50">
                </div>

                <div class="form_group" style="margin-bottom: 1.5rem;">
                    <label class="form_label" for="table_notes">Ghi Chú</label>
                    <input type="text" name="notes" id="table_notes" class="form_input" placeholder="Ví dụ: Bàn danh dự dành cho phụ mẫu..." maxlength="255">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <button type="button" class="mayhen_seat_btn mayhen_seat_btn--outline" onclick="document.getElementById('modal_add_table').style.display='none'">
                        Hủy Bỏ
                    </button>
                    <button type="submit" class="mayhen_seat_btn mayhen_seat_btn--gold">
                        + Tạo Bàn Tiệc
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/seating-planner.js') }}"></script>
@endpush

