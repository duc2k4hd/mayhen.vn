@extends('client.layouts.master')

@section('title', $project->title . ' — Workspace Tổ Chức Đám Cưới')
@section('meta_robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/wedding-journey.css') }}">
@endpush

@section('content')
<div class="mayhen_wedding_planner_page"
     data-wedding-page="workspace"
     data-project-slug="{{ $project->slug }}"
     data-floorplan-url="{{ route('wedding.workspace.floorplan.save', ['slug' => $project->slug]) }}"
     data-script-url="{{ route('wedding.api.generate_script') }}"
     data-floorplan="{{ json_encode($project->floorplan_json, JSON_UNESCAPED_UNICODE) }}">
    <div class="mayhen_wedding_planner_container">
        <section class="mayhen_wedding_planner_hero">
            <span class="mayhen_wedding_planner_eyebrow">Personal Wedding Workspace · {{ strtoupper($project->region_style) }}</span>
            <h1 class="mayhen_wedding_planner_h1">{{ $project->title }}</h1>
            <p class="mayhen_wedding_planner_lead">
                Ngày dự kiến: <strong>{{ $project->wedding_date ? $project->wedding_date->format('d/m/Y') : 'Chưa chốt ngày' }}</strong>
                @if ($daysLeft !== null)
                    · <strong>{{ $daysLeft >= 0 ? "Còn {$daysLeft} ngày" : 'Đã qua ngày lễ' }}</strong>
                @endif
                · Mục tiêu khách mời: <strong>{{ $project->guest_target }} khách</strong>
            </p>
            <div class="mayhen_wedding_planner_subnav">
                <a href="{{ route('wedding.workspace.index') }}" class="mayhen_wedding_planner_pill">← Danh sách hồ sơ</a>
                <a href="{{ route('wedding.dates.index') }}" class="mayhen_wedding_planner_pill">Xem Ngày Cưới</a>
                <a href="{{ route('templates.index') }}" class="mayhen_wedding_planner_pill mayhen_wedding_planner_pill--crimson">Tạo Thiệp Cưới Online →</a>
            </div>
        </section>

        {{-- KPI Summary Strip --}}
        <div class="mayhen_wedding_planner_grid_4" style="margin-bottom:22px;">
            <div class="mayhen_wedding_budget_kpi">
                <div class="mayhen_wedding_budget_kpi_label">Tiến độ Checklist</div>
                <div class="mayhen_wedding_budget_kpi_value mayhen_wedding_budget_kpi_value--jade">
                    {{ $project->tasks->where('status', 'done')->count() }} / {{ $project->tasks->count() }} việc
                </div>
            </div>
            <div class="mayhen_wedding_budget_kpi">
                <div class="mayhen_wedding_budget_kpi_label">Hợp đồng đã chốt (Committed)</div>
                <div class="mayhen_wedding_budget_kpi_value">{{ number_format($budgetSummary['total_committed_vnd'], 0, ',', '.') }} ₫</div>
            </div>
            <div class="mayhen_wedding_budget_kpi">
                <div class="mayhen_wedding_budget_kpi_label">Đã thanh toán (gồm cọc)</div>
                <div class="mayhen_wedding_budget_kpi_value mayhen_wedding_budget_kpi_value--jade">{{ number_format($budgetSummary['total_paid_vnd'], 0, ',', '.') }} ₫</div>
            </div>
            <div class="mayhen_wedding_budget_kpi">
                <div class="mayhen_wedding_budget_kpi_label">Còn phải trả (Outstanding)</div>
                <div class="mayhen_wedding_budget_kpi_value mayhen_wedding_budget_kpi_value--crimson">{{ number_format($budgetSummary['total_outstanding_vnd'], 0, ',', '.') }} ₫</div>
            </div>
        </div>

        {{-- Workspace Navigation Tabs --}}
        <div class="mayhen_wedding_planner_tabs" role="tablist">
            <button type="button" class="mayhen_wedding_planner_tab_btn is-active" data-tab-target="checklist">1. Checklist ({{ $project->tasks->count() }})</button>
            <button type="button" class="mayhen_wedding_planner_tab_btn" data-tab-target="budget">2. Ngân Sách VND</button>
            <button type="button" class="mayhen_wedding_planner_tab_btn" data-tab-target="seating">3. Sơ Đồ Bàn 2D</button>
            <button type="button" class="mayhen_wedding_planner_tab_btn" data-tab-target="timeline">4. Lịch Trình Ngày Cưới</button>
            <button type="button" class="mayhen_wedding_planner_tab_btn" data-tab-target="vendors">5. Nhà Cung Cấp ({{ $project->vendors->count() }})</button>
            <button type="button" class="mayhen_wedding_planner_tab_btn" data-tab-target="ai">6. AI Concierge &amp; Kịch Bản</button>
        </div>

        {{-- TAB 1: CHECKLIST --}}
        <section class="mayhen_wedding_planner_card mayhen_wedding_planner_tab_panel is-active" data-tab-panel="checklist">
            <h2 class="mayhen_wedding_planner_card_title">Checklist Tổ Chức Đám Cưới Thông Minh</h2>
            <p class="mayhen_wedding_planner_card_sub">Tự động kiểm tra phụ thuộc vòng lặp (Cycle Detection) và co giãn theo ngày cưới.</p>

            <form id="addTaskForm" action="{{ route('wedding.workspace.tasks.store', ['slug' => $project->slug]) }}" class="mayhen_wedding_planner_grid_4" style="align-items:end;margin-bottom:18px;">
                <div class="mayhen_wedding_planner_field" style="margin:0;">
                    <label class="mayhen_wedding_planner_label">Tên công việc *</label>
                    <input name="title" type="text" required placeholder="VD: Thử đồ váy cưới lần cuối" class="mayhen_wedding_planner_input">
                </div>
                <div class="mayhen_wedding_planner_field" style="margin:0;">
                    <label class="mayhen_wedding_planner_label">Giai đoạn</label>
                    <input name="phase" type="text" value="Hoàn thiện" class="mayhen_wedding_planner_input">
                </div>
                <div class="mayhen_wedding_planner_field" style="margin:0;">
                    <label class="mayhen_wedding_planner_label">Hạn hoàn thành</label>
                    <input name="due_date" type="date" class="mayhen_wedding_planner_input">
                </div>
                <button type="submit" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--primary">+ Thêm Việc</button>
            </form>

            <div class="mayhen_wedding_date_compare_table_wrap">
                <table class="mayhen_wedding_date_compare_table">
                    <thead><tr><th>Giai đoạn</th><th>Công việc</th><th>Phụ trách</th><th>Hạn chót</th><th>Trạng thái</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($project->tasks as $t)
                            <tr>
                                <td><span style="font-size:12px;color:#5F5750;">{{ $t->phase }}</span></td>
                                <td><strong>{{ $t->title }}</strong></td>
                                <td>{{ $t->assignee }}</td>
                                <td>{{ $t->due_date ? $t->due_date->format('d/m/Y') : '—' }}</td>
                                <td>
                                    <select class="mayhen_wedding_planner_select" style="padding:5px 8px;font-size:12.5px;"
                                            data-task-status-url="{{ route('wedding.workspace.tasks.update', ['slug' => $project->slug, 'taskId' => $t->id]) }}">
                                        <option value="todo" {{ $t->status === 'todo' ? 'selected' : '' }}>Chưa làm</option>
                                        <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>Đang làm</option>
                                        <option value="done" {{ $t->status === 'done' ? 'selected' : '' }}>Hoàn tất</option>
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--outline mayhen_wedding_planner_btn--sm"
                                            data-delete-url="{{ route('wedding.workspace.tasks.destroy', ['slug' => $project->slug, 'taskId' => $t->id]) }}">Xóa</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- TAB 2: BUDGET VND --}}
        <section class="mayhen_wedding_planner_card mayhen_wedding_planner_tab_panel" data-tab-panel="budget">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:14px;">
                <div>
                    <h2 class="mayhen_wedding_planner_card_title" style="margin:0;">Quản Lý Ngân Sách Đám Cưới (Số Nguyên VND)</h2>
                    <p class="mayhen_wedding_planner_card_sub" style="margin:4px 0 0;">Bất biến kế toán: Tiền cọc nằm trong cột <em>Đã thanh toán</em> và KHÔNG BAO GIỜ cộng trùng vào tổng <em>Hợp đồng đã chốt</em>.</p>
                </div>
                <a href="{{ route('wedding.workspace.expenses.export_csv', ['slug' => $project->slug]) }}" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--outline mayhen_wedding_planner_btn--sm">
                    Xuất File Excel (.CSV)
                </a>
            </div>

            @if ($budgetSummary['is_over_budget'])
                <div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--warn">
                    Cảnh báo vượt ngân sách dự kiến: <strong>+{{ number_format($budgetSummary['over_budget_vnd'], 0, ',', '.') }} ₫</strong>.
                </div>
            @endif

            <form id="addExpenseForm" action="{{ route('wedding.workspace.expenses.store', ['slug' => $project->slug]) }}" class="mayhen_wedding_planner_grid_4" style="align-items:end;margin-bottom:18px;">
                <div class="mayhen_wedding_planner_field" style="margin:0;">
                    <label class="mayhen_wedding_planner_label">Hạng mục</label>
                    <select name="category" class="mayhen_wedding_planner_select">
                        @foreach ($categories as $ck => $cm)
                            <option value="{{ $ck }}">{{ $cm['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mayhen_wedding_planner_field" style="margin:0;">
                    <label class="mayhen_wedding_planner_label">Tên khoản chi *</label>
                    <input name="title" type="text" required placeholder="VD: Đặt cọc sảnh tiệc ABC" class="mayhen_wedding_planner_input">
                </div>
                <div class="mayhen_wedding_planner_field" style="margin:0;">
                    <label class="mayhen_wedding_planner_label">Hợp đồng chốt (VND)</label>
                    <input name="committed_vnd" type="number" min="0" step="100000" value="0" class="mayhen_wedding_planner_input">
                </div>
                <div class="mayhen_wedding_planner_field" style="margin:0;">
                    <label class="mayhen_wedding_planner_label">Tiền cọc / Đã trả (VND)</label>
                    <input name="deposit_vnd" type="number" min="0" step="100000" value="0" class="mayhen_wedding_planner_input">
                </div>
                <div class="mayhen_wedding_planner_field" style="margin:0;">
                    <label class="mayhen_wedding_planner_label">Bên chi trả</label>
                    <select name="payer_side" class="mayhen_wedding_planner_select">
                        <option value="couple">Cặp đôi</option>
                        <option value="groom_family">Nhà trai</option>
                        <option value="bride_family">Nhà gái</option>
                    </select>
                </div>
                <button type="submit" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--primary">+ Thêm Khoản Chi</button>
            </form>

            <div class="mayhen_wedding_date_compare_table_wrap">
                <table class="mayhen_wedding_date_compare_table">
                    <thead><tr><th>Khoản chi</th><th>Bên trả</th><th>Ngân sách</th><th>Hợp đồng chốt</th><th>Cọc</th><th>Đã thanh toán</th><th>Còn phải trả</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($budgetSummary['items'] as $exp)
                            <tr>
                                <td><strong>{{ $exp['title'] }}</strong></td>
                                <td>{{ $exp['payer_side'] }}</td>
                                <td>{{ number_format($exp['budgeted_vnd'], 0, ',', '.') }} ₫</td>
                                <td><strong>{{ number_format($exp['committed_vnd'], 0, ',', '.') }} ₫</strong></td>
                                <td>{{ number_format($exp['deposit_vnd'], 0, ',', '.') }} ₫</td>
                                <td style="color:#1E4D3B;">{{ number_format($exp['paid_vnd'], 0, ',', '.') }} ₫</td>
                                <td style="color:#85182A;font-weight:700;">{{ number_format($exp['outstanding_vnd'], 0, ',', '.') }} ₫</td>
                                <td>
                                    <button type="button" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--outline mayhen_wedding_planner_btn--sm"
                                            data-delete-url="{{ route('wedding.workspace.expenses.destroy', ['slug' => $project->slug, 'expenseId' => $exp['id']]) }}">Xóa</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- TAB 3: 2D FLOOR PLAN & SEATING STUDIO --}}
        <section class="mayhen_wedding_planner_card mayhen_wedding_planner_tab_panel" data-tab-panel="seating">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                <div>
                    <h2 class="mayhen_wedding_planner_card_title" style="margin:0;">Studio Sơ Đồ Mặt Bằng &amp; Xếp Bàn Tiệc 2D</h2>
                    <p class="mayhen_wedding_planner_card_sub" style="margin:4px 0 0;">Kéo thả bàn trực tiếp trên mặt bằng, bấm vào bàn để gán khách mời, tự động phát hiện chồng lấn &amp; trùng khách.</p>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button type="button" id="addFloorTableBtn" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--outline mayhen_wedding_planner_btn--sm">+ Thêm Bàn</button>
                    <button type="button" id="saveFloorPlanBtn" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--primary mayhen_wedding_planner_btn--sm">Lưu &amp; Kiểm Tra Sơ Đồ</button>
                    <button type="button" id="exportFloorPngBtn" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--crimson mayhen_wedding_planner_btn--sm">Tải Ảnh PNG</button>
                </div>
            </div>

            <div id="floorPlanIssues">
                @foreach ($floorplanValidation['issues'] as $iss)
                    <div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--warn">{{ $iss['message'] }}</div>
                @endforeach
            </div>

            <div class="mayhen_wedding_planner_grid_2" style="grid-template-columns: minmax(0, 1fr) 320px;">
                <div class="mayhen_wedding_seating_stage_wrap">
                    <svg id="weddingFloorPlanSvg" class="mayhen_wedding_seating_svg" viewBox="0 0 840 480" aria-label="Sơ đồ bàn tiệc 2D"></svg>
                </div>
                <div style="background:#FAF5EB;border:1px solid #E5DAC8;border-radius:14px;padding:16px;">
                    <h3 style="margin:0 0 10px;font-size:17px;">Chỉnh Sửa Bàn Đang Chọn</h3>
                    <div id="selectedTableEditor"></div>
                </div>
            </div>
        </section>

        {{-- TAB 4: TIMELINE & CONFLICT DETECTOR --}}
        <section class="mayhen_wedding_planner_card mayhen_wedding_planner_tab_panel" data-tab-panel="timeline">
            <h2 class="mayhen_wedding_planner_card_title">Lịch Trình Ngày Cưới &amp; Phát Hiện Xung Đột</h2>
            <p class="mayhen_wedding_planner_card_sub">Tự động cảnh báo thời lượng âm, trùng lịch người phụ trách và thiếu khoảng đệm di chuyển dưới 15 phút giữa hai địa điểm.</p>

            @if ($timelineAnalysis['conflicts'] === [])
                <div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--info">Lịch trình hiện tại hợp lệ, không phát hiện xung đột giờ giấc hay thiếu đệm di chuyển.</div>
            @else
                @foreach ($timelineAnalysis['conflicts'] as $cf)
                    <div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--warn">{{ $cf['message'] }}</div>
                @endforeach
            @endif

            <form id="addTimelineForm" action="{{ route('wedding.workspace.timeline.store', ['slug' => $project->slug]) }}" class="mayhen_wedding_planner_grid_4" style="align-items:end;margin-bottom:18px;">
                <div class="mayhen_wedding_planner_field" style="margin:0;">
                    <label class="mayhen_wedding_planner_label">Hoạt động *</label>
                    <input name="title" type="text" required placeholder="VD: Chụp ảnh gia đình tại sảnh" class="mayhen_wedding_planner_input">
                </div>
                <div class="mayhen_wedding_planner_row_2">
                    <div class="mayhen_wedding_planner_field" style="margin:0;">
                        <label class="mayhen_wedding_planner_label">Bắt đầu</label>
                        <input name="start_time" type="time" value="11:30" required class="mayhen_wedding_planner_input">
                    </div>
                    <div class="mayhen_wedding_planner_field" style="margin:0;">
                        <label class="mayhen_wedding_planner_label">Kết thúc</label>
                        <input name="end_time" type="time" value="12:15" required class="mayhen_wedding_planner_input">
                    </div>
                </div>
                <div class="mayhen_wedding_planner_field" style="margin:0;">
                    <label class="mayhen_wedding_planner_label">Địa điểm / Phụ trách</label>
                    <input name="location" type="text" placeholder="Nhà hàng / Ekip chụp ảnh" class="mayhen_wedding_planner_input">
                    <input name="assignee" type="hidden" value="Ekip sự kiện">
                </div>
                <button type="submit" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--primary">+ Thêm Mốc Giờ</button>
            </form>

            @foreach ($project->timelineItems as $ti)
                <div class="mayhen_wedding_timeline_item">
                    <div class="mayhen_wedding_timeline_time">{{ $ti->start_time }} – {{ $ti->end_time }}</div>
                    <div>
                        <strong>{{ $ti->title }}</strong>
                        <div style="font-size:12.5px;color:#5F5750;">Địa điểm: {{ $ti->location ?: 'Chưa ghi' }} · Phụ trách: {{ $ti->assignee ?: 'Chưa gán' }}</div>
                    </div>
                    <button type="button" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--outline mayhen_wedding_planner_btn--sm"
                            data-delete-url="{{ route('wedding.workspace.timeline.destroy', ['slug' => $project->slug, 'itemId' => $ti->id]) }}">Xóa</button>
                </div>
            @endforeach
        </section>

        {{-- TAB 5: VENDORS --}}
        <section class="mayhen_wedding_planner_card mayhen_wedding_planner_tab_panel" data-tab-panel="vendors">
            <h2 class="mayhen_wedding_planner_card_title">So Sánh Nhà Cung Cấp &amp; Báo Giá Thực Tế</h2>
            <p class="mayhen_wedding_planner_card_sub">Ghi nhận báo giá do chính bạn làm việc với nhà cung cấp để so sánh ưu/nhược điểm khách quan.</p>

            <form id="addVendorForm" action="{{ route('wedding.workspace.vendors.store', ['slug' => $project->slug]) }}" class="mayhen_wedding_planner_grid_4" style="align-items:end;margin-bottom:18px;">
                <div class="mayhen_wedding_planner_field" style="margin:0;">
                    <label class="mayhen_wedding_planner_label">Nhóm dịch vụ</label>
                    <select name="category" class="mayhen_wedding_planner_select">
                        @foreach ($categories as $ck => $cm)
                            <option value="{{ $ck }}">{{ $cm['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mayhen_wedding_planner_field" style="margin:0;">
                    <label class="mayhen_wedding_planner_label">Tên nhà cung cấp *</label>
                    <input name="name" type="text" required placeholder="VD: Trung tâm tiệc cưới XYZ" class="mayhen_wedding_planner_input">
                </div>
                <div class="mayhen_wedding_planner_field" style="margin:0;">
                    <label class="mayhen_wedding_planner_label">Báo giá (VND)</label>
                    <input name="quote_vnd" type="number" min="0" step="100000" value="0" class="mayhen_wedding_planner_input">
                </div>
                <button type="submit" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--primary">+ Lưu Báo Giá</button>
            </form>

            <div class="mayhen_wedding_date_compare_table_wrap">
                <table class="mayhen_wedding_date_compare_table">
                    <thead><tr><th>Nhà cung cấp</th><th>Nhóm</th><th>Báo giá</th><th>Trạng thái</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($project->vendors as $v)
                            <tr>
                                <td><strong>{{ $v->name }}</strong><div style="font-size:12px;color:#5F5750;">{{ $v->phone }}</div></td>
                                <td>{{ $categories[$v->category]['label'] ?? $v->category }}</td>
                                <td><strong>{{ number_format((int) $v->quote_vnd, 0, ',', '.') }} ₫</strong></td>
                                <td>{{ $v->status }}</td>
                                <td>
                                    <button type="button" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--outline mayhen_wedding_planner_btn--sm"
                                            data-delete-url="{{ route('wedding.workspace.vendors.destroy', ['slug' => $project->slug, 'vendorId' => $v->id]) }}">Xóa</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="color:#5F5750;">Chưa có nhà cung cấp nào được lưu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- TAB 6: AI WEDDING CONCIERGE --}}
        <section class="mayhen_wedding_planner_card mayhen_wedding_planner_tab_panel" data-tab-panel="ai">
            <h2 class="mayhen_wedding_planner_card_title">AI Wedding Concierge (Có Căn Cứ Dữ Liệu Workspace)</h2>
            <p class="mayhen_wedding_planner_card_sub">Còn <strong>{{ $planSummary['remaining_ai_today'] }}/{{ $planSummary['daily_ai'] }}</strong> lượt AI hôm nay ({{ $planSummary['tier_label'] }}). AI chỉ đề xuất bản nháp — tuyệt đối không tự ý sửa dữ liệu hồ sơ của bạn.</p>

            <div class="mayhen_wedding_planner_grid_equal">
                <form id="weddingScriptForm">
                    <div class="mayhen_wedding_planner_field">
                        <label class="mayhen_wedding_planner_label">Yêu cầu biên soạn / phân tích</label>
                        <select name="script_type" class="mayhen_wedding_planner_select">
                            @foreach ($scriptTypes as $sk => $sl)
                                <option value="{{ $sk }}">{{ $sl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="groom_name" value="{{ $project->groom_name }}">
                    <input type="hidden" name="bride_name" value="{{ $project->bride_name }}">
                    <input type="hidden" name="wedding_date" value="{{ $project->wedding_date ? $project->wedding_date->format('d/m/Y') : '' }}">
                    <input type="hidden" name="region" value="{{ $project->region_style }}">
                    <div class="mayhen_wedding_planner_field">
                        <label class="mayhen_wedding_planner_label">Ghi chú bổ sung cho trợ lý</label>
                        <textarea name="custom_notes" rows="3" placeholder="VD: Muốn tối ưu tiến độ 30 ngày cuối, hoặc viết lời cảm ơn ngắn gọn..." class="mayhen_wedding_planner_textarea"></textarea>
                    </div>
                    <label class="mayhen_wedding_planner_check">
                        <input type="checkbox" name="use_ai" value="1" checked>
                        <span>Sử dụng AI Wedding Concierge phân tích trực tiếp trên dữ liệu dự án này</span>
                    </label>
                    <button type="submit" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--crimson">Tạo Bản Đề Xuất Ngay</button>
                </form>

                <div id="weddingScriptOutput">
                    @if ($project->scripts->isNotEmpty())
                        <div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--info">Bản lưu gần nhất: <strong>{{ $project->scripts->first()->title }}</strong></div>
                        <pre style="white-space:pre-wrap;font-family:inherit;background:#FFFDF9;border:1px solid #E5DAC8;border-radius:12px;padding:14px;font-size:13.5px;line-height:1.65;">{{ $project->scripts->first()->content }}</pre>
                    @else
                        <div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--info">Chọn một loại kịch bản hoặc phân tích tiến độ rồi bấm <strong>Tạo Bản Đề Xuất Ngay</strong>.</div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>

<div id="mayhenWeddingOverlay" class="mayhen_wedding_planner_overlay" role="status" aria-live="polite">
    <div class="mayhen_wedding_planner_overlay_box">
        <div class="mayhen_wedding_planner_spinner"></div>
        <h3 data-overlay-title style="margin:0 0 8px;font-size:20px;">Đang xử lý...</h3>
        <p data-overlay-desc style="margin:0;font-size:13.5px;color:#5F5750;">Vui lòng đợi trong giây lát.</p>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset_v('assets/client/js/pages/wedding-journey.js') }}" defer></script>
@endpush
