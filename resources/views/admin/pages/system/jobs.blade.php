@extends('admin.layouts.master')

@section('title', 'Trung Tâm Chỉ Huy Tác Vụ Nền & Hàng Đợi')

@section('content')
<div class="mayhen_admin_page_header" style="margin-bottom: 24px;">
    <div class="mayhen_admin_page_header_left">
        <h1 class="mayhen_admin_page_title">Job Center — Chỉ Huy Tác Vụ Nền & Hàng Đợi</h1>
        <p class="mayhen_admin_page_subtitle">Giám sát tiến độ các lô tác vụ xử lý ảnh, nhập khách mời, gửi thiệp và phục hồi lỗi hàng đợi (Mục 18 & 48 PRD V3)</p>
    </div>
    <div class="mayhen_admin_page_header_right" style="display: flex; gap: 8px;">
        <a href="{{ route('admin.jobs.export_errors') }}" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="font-size: 13px; padding: 8px 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Xuất Báo Cáo Lỗi (CSV)
        </a>
        <a href="{{ request()->fullUrl() }}" class="mayhen_admin_btn mayhen_admin_btn--primary" style="font-size: 13px; padding: 8px 16px; text-decoration: none;">
            Làm Mới
        </a>
    </div>
</div>

<!-- 4 Thẻ KPI Tổng Thể -->
<div class="mayhen_admin_kpi_grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="mayhen_admin_kpi_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px; border-left: 4px solid {{ $kpis['total_failed'] > 0 ? '#C0392B' : '#27AE60' }};">
        <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Tác Vụ Lỗi (Failed Jobs)</div>
        <div style="font-size: 26px; font-weight: 700; color: {{ $kpis['total_failed'] > 0 ? '#C0392B' : '#27AE60' }};">
            {{ number_format($kpis['total_failed'], 0, ',', '.') }}
        </div>
        <div style="font-size: 12px; color: #7A7265; margin-top: 4px;">
            {{ $kpis['total_failed'] > 0 ? 'Cần kiểm tra hoặc thử lại' : 'Hệ thống vận hành trơn tru' }}
        </div>
    </div>

    <div class="mayhen_admin_kpi_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px; border-left: 4px solid #0284C7;">
        <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Chờ Trong Hàng Đợi (Pending)</div>
        <div style="font-size: 26px; font-weight: 700; color: #0284C7;">
            {{ number_format($kpis['total_pending'], 0, ',', '.') }}
        </div>
        <div style="font-size: 12px; color: #7A7265; margin-top: 4px;">
            Tác vụ đang đợi worker xử lý
        </div>
    </div>

    <div class="mayhen_admin_kpi_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px; border-left: 4px solid #9C7A4A;">
        <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Lô Đang Chạy (Active Batches)</div>
        <div style="font-size: 26px; font-weight: 700; color: #9C7A4A;">
            {{ number_format($kpis['active_batches'], 0, ',', '.') }}
        </div>
        <div style="font-size: 12px; color: #7A7265; margin-top: 4px;">
            Tiến trình đa tác vụ nền
        </div>
    </div>

    <div class="mayhen_admin_kpi_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px; border-left: 4px solid #2D6A4F;">
        <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Lô Hoàn Tất (24h Qua)</div>
        <div style="font-size: 26px; font-weight: 700; color: #2D6A4F;">
            {{ number_format($kpis['completed_batches_24h'], 0, ',', '.') }}
        </div>
        <div style="font-size: 12px; color: #7A7265; margin-top: 4px;">
            {{ $kpis['failed_batches_24h'] > 0 ? $kpis['failed_batches_24h'] . ' lô có lỗi phát sinh' : '100% lô thành công' }}
        </div>
    </div>
</div>

<!-- Thông báo Flash Messages -->
@if(session('success'))
    <div style="background: #E8F5E9; border: 1px solid #C8E6C9; border-left: 4px solid #2E7D32; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; color: #1B5E20; font-size: 13.5px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background: #FFEBEE; border: 1px solid #FFCDD2; border-left: 4px solid #C62828; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; color: #B71C1C; font-size: 13.5px;">
        {{ session('error') }}
    </div>
@endif

@if(session('info'))
    <div style="background: #E1F5FE; border: 1px solid #B3E5FC; border-left: 4px solid #0288D1; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; color: #01579B; font-size: 13.5px;">
        {{ session('info') }}
    </div>
@endif

<!-- THANH ĐIỀU HƯỚNG TABS -->
<div style="display: flex; gap: 8px; border-bottom: 2px solid #EFE8DD; margin-bottom: 24px;">
    <a href="{{ route('admin.jobs.index', ['tab' => 'batches']) }}" 
       style="padding: 10px 20px; font-size: 14px; font-weight: 600; text-decoration: none; color: {{ $currentTab === 'batches' ? '#1F1D1A' : '#7A7265' }}; border-bottom: 2px solid {{ $currentTab === 'batches' ? '#1F1D1A' : 'transparent' }}; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 6px;">
        Lô Tác Vụ Nền (Job Batches)
        <span style="font-size: 11px; padding: 2px 7px; border-radius: 10px; background: {{ $currentTab === 'batches' ? '#F4F0EA' : '#EFE8DD' }};">
            {{ $kpis['active_batches'] }} đang chạy
        </span>
    </a>

    <a href="{{ route('admin.jobs.index', ['tab' => 'failed']) }}" 
       style="padding: 10px 20px; font-size: 14px; font-weight: 600; text-decoration: none; color: {{ $currentTab === 'failed' ? '#C0392B' : '#7A7265' }}; border-bottom: 2px solid {{ $currentTab === 'failed' ? '#C0392B' : 'transparent' }}; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 6px;">
        Tác Vụ Thất Bại (Failed Jobs)
        @if($kpis['total_failed'] > 0)
            <span style="font-size: 11px; padding: 2px 7px; border-radius: 10px; background: #C0392B; color: #FFFFFF; font-weight: 700;">
                {{ $kpis['total_failed'] }}
            </span>
        @endif
    </a>

    <a href="{{ route('admin.jobs.index', ['tab' => 'pending']) }}" 
       style="padding: 10px 20px; font-size: 14px; font-weight: 600; text-decoration: none; color: {{ $currentTab === 'pending' ? '#0284C7' : '#7A7265' }}; border-bottom: 2px solid {{ $currentTab === 'pending' ? '#0284C7' : 'transparent' }}; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 6px;">
        Hàng Đợi Chờ Xử Lý (Queue Pending)
        <span style="font-size: 11px; padding: 2px 7px; border-radius: 10px; background: {{ $currentTab === 'pending' ? '#E0F2FE' : '#EFE8DD' }}; color: {{ $currentTab === 'pending' ? '#0369A1' : '#7A7265' }};">
            {{ $kpis['total_pending'] }}
        </span>
    </a>
</div>

{{-- ========================================================================= --}}
{{-- TAB 1: LÔ TÁC VỤ NỀN (JOB BATCHES)                                        --}}
{{-- ========================================================================= --}}
@if($currentTab === 'batches')
    <!-- Bộ Lọc Batches -->
    <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('admin.jobs.index') }}" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
            <input type="hidden" name="tab" value="batches">
            <div style="flex: 1; min-width: 240px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên lô công việc hoặc mã Batch ID..." class="mayhen_admin_input" style="width: 100%;">
            </div>
            <div style="width: 170px;">
                <select name="status" class="mayhen_admin_select" style="width: 100%;" onchange="this.form.submit()">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang chạy</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Thành công 100%</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Có lỗi thất bại</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã bị hủy</option>
                </select>
            </div>
            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="height: 38px; padding: 0 16px;">
                Lọc Lô
            </button>
            @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('admin.jobs.index', ['tab' => 'batches']) }}" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="height: 38px; padding: 0 12px; text-decoration: none; display: inline-flex; align-items: center;">
                    Xóa Lọc
                </a>
            @endif
        </form>
    </div>

    <!-- Bảng Danh Sách Batches -->
    <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #FAF8F5; border-bottom: 1px solid #EFE8DD; color: #7A7265; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                        <th style="padding: 12px 16px;">Tên Lô & Batch ID</th>
                        <th style="padding: 12px 16px; width: 260px;">Tiến Độ Hoàn Thành</th>
                        <th style="padding: 12px 16px; width: 140px; text-align: center;">Số Phần Tử</th>
                        <th style="padding: 12px 16px; width: 160px;">Thời Gian</th>
                        <th style="padding: 12px 16px; width: 130px; text-align: center;">Trạng Thái</th>
                        <th style="padding: 12px 16px; width: 150px; text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches ?? [] as $batch)
                        <tr style="border-bottom: 1px solid #F5F1EB; font-size: 13.5px;">
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <div style="font-weight: 600; color: #1F1D1A; margin-bottom: 3px;">
                                    {{ $batch->name }}
                                </div>
                                <code style="font-size: 11px; color: #7A7265; background: #FAF8F5; padding: 2px 6px; border-radius: 3px;">{{ $batch->id }}</code>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px; font-weight: 600;">
                                    <span>{{ $batch->progress }}%</span>
                                    <span style="color: #7A7265; font-weight: normal;">{{ $batch->processed_jobs }} / {{ $batch->total_jobs }} tác vụ</span>
                                </div>
                                <div style="width: 100%; height: 7px; background: #EFE8DD; border-radius: 4px; overflow: hidden;">
                                    <div style="width: {{ $batch->progress }}%; height: 100%; background: {{ $batch->failed_jobs > 0 ? '#C0392B' : ($batch->progress === 100 ? '#27AE60' : '#0284C7') }}; transition: width 0.3s ease;"></div>
                                </div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; text-align: center;">
                                <div style="font-size: 13px; font-weight: 600;">{{ $batch->total_jobs }}</div>
                                @if($batch->failed_jobs > 0)
                                    <div style="font-size: 11.5px; color: #C0392B; font-weight: 600;">
                                        {{ $batch->failed_jobs }} lỗi
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; font-size: 12px;">
                                <div style="color: #1F1D1A;">Tạo: {{ $batch->created_at_human }}</div>
                                @if($batch->finished_at_human)
                                    <div style="color: #7A7265; font-size: 11.5px;">Xong: {{ $batch->finished_at_human }}</div>
                                @endif
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; text-align: center;">
                                <span class="mayhen_admin_badge {{ $batch->status_badge }}" style="font-size: 11px; padding: 3px 8px; border-radius: 12px;">
                                    {{ $batch->status_label }}
                                </span>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; text-align: right; white-space: nowrap;">
                                @if($batch->status === 'running')
                                    <form method="POST" action="{{ route('admin.jobs.batches.cancel', $batch->id) }}" style="display: inline-block;" onsubmit="return confirm('Bạn có chắc chắn muốn HỦY BỎ lô công việc đang chạy này? Các tác vụ chưa xử lý sẽ bị dừng lại.');">
                                        @csrf
                                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--danger" style="padding: 4px 8px; font-size: 12px;">
                                            Hủy Lô
                                        </button>
                                    </form>
                                @endif

                                @if($batch->failed_jobs > 0)
                                    <form method="POST" action="{{ route('admin.jobs.batches.retry', $batch->id) }}" style="display: inline-block;" onsubmit="return confirm('Bạn có chắc chắn muốn đẩy lại {{ $batch->failed_jobs }} tác vụ lỗi trong lô này vào hàng đợi?');">
                                        @csrf
                                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="padding: 4px 8px; font-size: 12px;">
                                            Thử Lại Lỗi
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 40px; text-align: center; color: #8C8476;">
                                Hiện chưa có lô công việc nào trong hệ thống.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($batches) && $batches->hasPages())
            <div style="padding: 16px; border-top: 1px solid #EFE8DD;">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
@endif

{{-- ========================================================================= --}}
{{-- TAB 2: TÁC VỤ THẤT BẠI (FAILED JOBS)                                      --}}
{{-- ========================================================================= --}}
@if($currentTab === 'failed')
    <!-- Bộ Lọc & Công Cụ Thao Tác Hàng Loạt -->
    <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px;">
            <form method="GET" action="{{ route('admin.jobs.index') }}" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; flex: 1;">
                <input type="hidden" name="tab" value="failed">
                <div style="flex: 1; min-width: 220px;">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên job, exception, queue..." class="mayhen_admin_input" style="width: 100%;">
                </div>
                <div style="min-width: 160px;">
                    <select name="queue" class="mayhen_admin_select" style="width: 100%;" onchange="this.form.submit()">
                        <option value="">Tất cả hàng đợi</option>
                        @foreach($kpis['queues_with_failed'] as $q)
                            <option value="{{ $q }}" {{ request('queue') === $q ? 'selected' : '' }}>{{ $q }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="height: 38px; padding: 0 16px;">
                    Lọc Lỗi
                </button>
                @if(request()->anyFilled(['search', 'queue']))
                    <a href="{{ route('admin.jobs.index', ['tab' => 'failed']) }}" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="height: 38px; padding: 0 12px; text-decoration: none; display: inline-flex; align-items: center;">
                        Xóa Lọc
                    </a>
                @endif
            </form>

            @if($kpis['total_failed'] > 0)
                <div style="display: flex; gap: 8px;">
                    <form method="POST" action="{{ route('admin.jobs.retry_all') }}" onsubmit="return confirm('CẢNH BÁO: Thao tác này sẽ thử lại TẤT CẢ {{ $kpis['total_failed'] }} tác vụ lỗi. Tiếp tục?');">
                        @csrf
                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="height: 38px; padding: 0 14px; font-size: 13px;">
                            🔄 Thử Lại Tất Cả
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.jobs.purge') }}" onsubmit="return confirm('CẢNH BÁO QUAN TRỌNG: Thao tác này sẽ XÓA SẠCH VĨNH VIỄN toàn bộ danh sách tác vụ lỗi khỏi hệ thống và không thể khôi phục. Bạn có chắc chắn muốn xóa?');">
                        @csrf
                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--danger" style="height: 38px; padding: 0 14px; font-size: 13px;">
                            🗑️ Xóa Sạch Toàn Bộ
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- Bảng Danh Sách Failed Jobs -->
    <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #FAF8F5; border-bottom: 1px solid #EFE8DD; color: #7A7265; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                        <th style="padding: 12px 16px; width: 140px;">Hàng Đợi & Loại</th>
                        <th style="padding: 12px 16px;">Tên Tác Vụ & Lỗi Rút Gọn</th>
                        <th style="padding: 12px 16px; width: 130px; text-align: center;">Thời Điểm Lỗi</th>
                        <th style="padding: 12px 16px; width: 190px; text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($failedJobs ?? [] as $job)
                        <tr style="border-bottom: 1px solid #F5F1EB; font-size: 13.5px;">
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <div style="margin-bottom: 4px;">
                                    <span class="mayhen_admin_badge {{ $job->category['badge_class'] ?? 'mayhen_admin_badge--secondary' }}" style="font-size: 11px; padding: 2px 8px; border-radius: 4px; display: inline-block;">
                                        {{ $job->category['name'] }}
                                    </span>
                                </div>
                                <code style="font-size: 11.5px; color: #5C5449; background: #F8F5EE; padding: 2px 6px; border-radius: 3px;">{{ $job->queue }}</code>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <div style="font-weight: 600; color: #1F1D1A; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                    {{ $job->display_name }}
                                    @if($job->is_high_risk)
                                        <span title="Tác vụ nhạy cảm: Chú ý tránh gửi lặp tin nhắn hoặc thanh toán kép" style="background: #FFF3CD; color: #856404; font-size: 10.5px; padding: 1px 6px; border-radius: 3px; font-weight: normal; border: 1px solid #FFEBAA;">
                                            ⚠️ Nhạy Cảm (Idempotency)
                                        </span>
                                    @endif
                                </div>
                                <div style="color: #C0392B; font-family: Consolas, Monaco, monospace; font-size: 12px; background: #FCF8F8; padding: 6px 10px; border-radius: 4px; border: 1px solid #F6DCD8; margin-top: 4px; word-break: break-all;">
                                    {{ $job->sanitized_exception_snippet }}
                                </div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; text-align: center; font-size: 12px; color: #7A7265;">
                                {{ $job->failed_at_human }}
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; text-align: right; white-space: nowrap;">
                                <button type="button" 
                                    onclick="viewJobDetails('{{ $job->uuid }}')" 
                                    class="mayhen_admin_btn mayhen_admin_btn--secondary" 
                                    style="padding: 4px 8px; font-size: 12px; margin-right: 4px;">
                                    Xem Lỗi
                                </button>

                                <form method="POST" action="{{ route('admin.jobs.retry', $job->uuid) }}" style="display: inline-block;" onsubmit="return confirmRetry('{{ $job->is_high_risk }}', '{{ $job->display_name }}');">
                                    @csrf
                                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="padding: 4px 10px; font-size: 12px;">
                                        Thử Lại
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.jobs.destroy', $job->uuid) }}" style="display: inline-block; margin-left: 4px;" onsubmit="return confirm('Bạn có chắc muốn xóa tác vụ lỗi này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--danger" style="padding: 4px 8px; font-size: 12px;">
                                        Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding: 40px; text-align: center; color: #27AE60;">
                                <div style="font-size: 26px; margin-bottom: 8px;">✓</div>
                                Tuyệt vời! Hiện không có tác vụ nào bị thất bại trong hệ thống.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($failedJobs) && $failedJobs->hasPages())
            <div style="padding: 16px; border-top: 1px solid #EFE8DD;">
                {{ $failedJobs->links() }}
            </div>
        @endif
    </div>
@endif

{{-- ========================================================================= --}}
{{-- TAB 3: HÀNG ĐỢI CHỜ XỬ LÝ (QUEUE PENDING)                                 --}}
{{-- ========================================================================= --}}
@if($currentTab === 'pending')
    <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #FAF8F5; border-bottom: 1px solid #EFE8DD; color: #7A7265; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                        <th style="padding: 12px 16px; width: 140px;">Hàng Đợi (Queue)</th>
                        <th style="padding: 12px 16px;">Tên Tác Vụ</th>
                        <th style="padding: 12px 16px; width: 100px; text-align: center;">Số Lần Thử</th>
                        <th style="padding: 12px 16px; width: 160px;">Thời Gian Lên Lịch</th>
                        <th style="padding: 12px 16px; width: 120px; text-align: center;">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingJobs ?? [] as $job)
                        <tr style="border-bottom: 1px solid #F5F1EB; font-size: 13.5px;">
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <span class="mayhen_admin_badge {{ $job->job_type['badge_class'] ?? 'mayhen_admin_badge--secondary' }}" style="font-size: 11px; padding: 2px 8px; border-radius: 4px; display: inline-block;">
                                    {{ $job->job_type['name'] }}
                                </span>
                                <div style="font-family: monospace; font-size: 11px; color: #7A7265; margin-top: 2px;">{{ $job->queue }}</div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <div style="font-weight: 600; color: #1F1D1A;">
                                    {{ $job->display_name }}
                                </div>
                                <div style="font-size: 11.5px; color: #7A7265; margin-top: 2px;">
                                    Khởi tạo lúc: {{ $job->created_at_human }}
                                </div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; text-align: center; font-weight: 600;">
                                {{ $job->attempts }}
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; font-size: 12px; color: #7A7265;">
                                {{ $job->available_at_human }}
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; text-align: center;">
                                @if($job->is_reserved)
                                    <span class="mayhen_admin_badge mayhen_admin_badge--info" style="font-size: 11px; padding: 2px 8px; border-radius: 12px;">
                                        Worker Đang Xử Lý
                                    </span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge--secondary" style="font-size: 11px; padding: 2px 8px; border-radius: 12px;">
                                        Đang Chờ
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 40px; text-align: center; color: #8C8476;">
                                Hàng đợi hiện đang rỗng. Tất cả tác vụ nền đã được xử lý xong.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($pendingJobs) && $pendingJobs->hasPages())
            <div style="padding: 16px; border-top: 1px solid #EFE8DD;">
                {{ $pendingJobs->links() }}
            </div>
        @endif
    </div>
@endif

<!-- MODAL XEM CHI TIẾT SANITIZED EXCEPTION -->
<div id="job-details-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #FFFFFF; border-radius: 8px; width: 90%; max-width: 800px; max-height: 85vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <div style="padding: 16px 20px; border-bottom: 1px solid #EFE8DD; background: #FAF8F5; display: flex; justify-content: space-between; align-items: center;">
            <h3 id="modal-title" style="margin: 0; font-size: 15px; font-weight: 600; color: #1F1D1A;">Chi Tiết Tác Vụ Lỗi</h3>
            <button type="button" onclick="closeJobDetailsModal()" style="border: none; background: transparent; font-size: 20px; cursor: pointer; color: #7A7265;">&times;</button>
        </div>
        <div style="padding: 20px; overflow-y: auto; flex: 1;">
            <div style="margin-bottom: 14px;">
                <label style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.05em; color: #7A7265; font-weight: 600; display: block; margin-bottom: 4px;">TÊN TÁC VỤ</label>
                <div id="modal-job-name" style="font-weight: 600; font-size: 14px; color: #1F1D1A;"></div>
            </div>
            <div style="margin-bottom: 14px;">
                <label style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.05em; color: #7A7265; font-weight: 600; display: block; margin-bottom: 4px;">STACK TRACE (ĐÃ KHỬ SẠCH THÔNG TIN BÍ MẬT)</label>
                <pre id="modal-stack-trace" style="background: #1F1D1A; color: #F8F5EE; padding: 14px; border-radius: 6px; font-family: Consolas, Monaco, monospace; font-size: 12px; line-height: 1.5; overflow-x: auto; max-height: 380px; white-space: pre-wrap; word-break: break-all;"></pre>
            </div>
        </div>
        <div style="padding: 12px 20px; border-top: 1px solid #EFE8DD; background: #FAF8F5; text-align: right;">
            <button type="button" onclick="closeJobDetailsModal()" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="padding: 6px 16px; font-size: 13px;">
                Đóng
            </button>
        </div>
    </div>
</div>

<script>
function confirmRetry(isHighRisk, jobName) {
    if (isHighRisk === '1' || isHighRisk === true) {
        return confirm('⚠️ CẢNH BÁO TRÙNG LẶP (IDEMPOTENCY WARNING):\n\nTác vụ "' + jobName + '" có thể liên quan đến việc gửi Email/SMS/Zalo hoặc xử lý thanh toán.\n\nViệc thử lại có nguy cơ gửi trùng thư mời đến khách hoặc xử lý thanh toán lặp nếu tác vụ trước đó đã hoàn tất một phần.\n\nBạn có chắc chắn muốn tiếp tục thử lại?');
    }
    return confirm('Bạn có chắc muốn đẩy tác vụ này vào hàng đợi để thử lại?');
}

function viewJobDetails(uuid) {
    const modal = document.getElementById('job-details-modal');
    const titleEl = document.getElementById('modal-job-name');
    const traceEl = document.getElementById('modal-stack-trace');

    modal.style.display = 'flex';
    titleEl.textContent = 'Đang tải thông tin...';
    traceEl.textContent = 'Đang giải mã và khử thông tin bí mật...';

    fetch('/admin/system/jobs/' + uuid + '/details', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.job) {
            titleEl.textContent = data.job.display_name;
            traceEl.textContent = data.job.sanitized_exception || 'Không có stack trace.';
        } else {
            traceEl.textContent = 'Không tìm thấy chi tiết tác vụ.';
        }
    })
    .catch(() => {
        traceEl.textContent = 'Có lỗi khi kết nối máy chủ.';
    });
}

function closeJobDetailsModal() {
    document.getElementById('job-details-modal').style.display = 'none';
}
</script>
@endsection
