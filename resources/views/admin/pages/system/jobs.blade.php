@extends('admin.layouts.master')

@section('title', 'Giám Sát Hàng Đợi & Tác Vụ Lỗi — Mây Hẹn')
@section('page_title', 'Giám Sát Hàng Đợi & Tác Vụ Lỗi (Failed Jobs)')

@section('content')
    {{-- KPI Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid {{ $kpis['total_failed'] > 0 ? '#C0392B' : '#27AE60' }};">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Tổng Tác Vụ Lỗi (Failed Jobs)
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: {{ $kpis['total_failed'] > 0 ? '#C0392B' : '#27AE60' }};">
                {{ number_format($kpis['total_failed'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">
                {{ $kpis['total_failed'] > 0 ? 'Cần xử lý hoặc thử lại' : 'Hệ thống hoạt động trơn tru' }}
            </div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Hàng Đợi Có Lỗi (Active Queues)
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                {{ count($kpis['queues']) }}
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">
                {{ $kpis['queues']->join(', ') ?: 'Không có lỗi' }}
            </div>
        </div>
    </div>

    {{-- Actions & Flash Messages --}}
    @if(session('success'))
        <div class="mayhen_admin_alert mayhen_admin_alert--success" style="margin-bottom: 16px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mayhen_admin_alert mayhen_admin_alert--danger" style="margin-bottom: 16px;">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="mayhen_admin_alert mayhen_admin_alert--info" style="margin-bottom: 16px;">{{ session('info') }}</div>
    @endif

    {{-- Bộ lọc & Công cụ thao tác hàng loạt --}}
    <div class="mayhen_admin_card" style="margin-bottom: 20px;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 12px;">
            <form method="GET" action="{{ route('admin.jobs.index') }}" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; flex: 1;">
                <div style="flex: 1; min-width: 220px;">
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--mh-admin-muted); display: block; margin-bottom: 4px;">TÌM KIẾM PAYLOAD / EXCEPTION</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Tên job, lỗi exception..."
                           style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px; font-size: 0.875rem;">
                </div>

                <div style="min-width: 160px;">
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--mh-admin-muted); display: block; margin-bottom: 4px;">HÀNG ĐỢI (QUEUE)</label>
                    <select name="queue" style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px; font-size: 0.875rem;">
                        <option value="">Tất cả hàng đợi</option>
                        @foreach($kpis['queues'] as $q)
                            <option value="{{ $q }}" {{ request('queue') === $q ? 'selected' : '' }}>{{ $q }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="padding: 8px 16px;">Lọc</button>
                    <a href="{{ route('admin.jobs.index') }}" class="mayhen_admin_btn mayhen_admin_btn--ghost" style="padding: 8px 16px;">Xóa lọc</a>
                </div>
            </form>

            @if($kpis['total_failed'] > 0)
                <div style="display: flex; gap: 8px;">
                    <form method="POST" action="{{ route('admin.jobs.retry_all') }}" onsubmit="return confirm('Bạn có chắc muốn đẩy lại TẤT CẢ các tác vụ thất bại vào queue?')">
                        @csrf
                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="padding: 8px 16px;">
                            🔄 Thử Lại Tất Cả
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.jobs.purge') }}" onsubmit="return confirm('CẢNH BÁO: Thao tác này sẽ XÓA TOÀN BỘ các tác vụ lỗi khỏi hệ thống và không thể khôi phục. Tiếp tục?')">
                        @csrf
                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--danger" style="padding: 8px 16px;">
                            🗑️ Xóa Sạch Toàn Bộ
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- Bảng danh sách Jobs --}}
    <div class="mayhen_admin_card" style="padding: 0; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--mh-admin-border); display: flex; align-items: center; justify-content: space-between;">
            <span style="font-weight: 700; font-size: 0.875rem;">{{ $jobs->total() }} tác vụ thất bại</span>
        </div>

        @if($jobs->isEmpty())
            <div style="padding: 48px; text-align: center; color: var(--mh-admin-muted);">
                <div style="font-size: 2.2rem; margin-bottom: 12px;">✅</div>
                <div style="font-weight: 600; font-size: 1rem; color: var(--mh-admin-charcoal);">Tuyệt vời! Không có tác vụ lỗi nào</div>
                <div style="font-size: 0.85rem; margin-top: 4px;">Queue worker đang xử lý mọi tác vụ nền hoàn hảo.</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="mayhen_admin_table">
                    <thead>
                        <tr>
                            <th>Tác Vụ (Job Class)</th>
                            <th>Hàng Đợi</th>
                            <th>Kết Nối</th>
                            <th>Thời Điểm Lỗi</th>
                            <th>Chi Tiết Exception</th>
                            <th style="text-align: right;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobs as $job)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; font-size: 0.85rem; color: var(--mh-admin-charcoal);">
                                        {{ class_basename($job->display_name) }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted); font-family: monospace;" title="{{ $job->display_name }}">
                                        {{ Str::limit($job->display_name, 45) }}
                                    </div>
                                </td>
                                <td>
                                    <span class="mayhen_admin_badge mayhen_admin_badge--info">{{ $job->queue }}</span>
                                </td>
                                <td style="font-size: 0.8rem;">
                                    <code>{{ $job->connection }}</code>
                                </td>
                                <td style="font-size: 0.8rem; white-space: nowrap;">
                                    {{ \Carbon\Carbon::parse($job->failed_at)->format('d/m/Y H:i:s') }}
                                </td>
                                <td style="max-width: 320px;">
                                    <details>
                                        <summary style="cursor: pointer; font-size: 0.75rem; color: #C0392B; font-weight: 600;">
                                            {{ Str::limit($job->exception, 80) }}
                                        </summary>
                                        <div style="margin-top: 8px; padding: 10px; background: #2b2b2b; color: #e6e6e6; border-radius: 6px; font-family: monospace; font-size: 0.7rem; max-height: 200px; overflow-y: auto; white-space: pre-wrap;">
                                            {{ $job->exception }}
                                        </div>
                                    </details>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <form method="POST" action="{{ route('admin.jobs.retry', $job->uuid) }}">
                                            @csrf
                                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="padding: 4px 10px; font-size: 0.75rem;" title="Đẩy lại tác vụ này vào queue">
                                                🔄 Thử lại
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.jobs.destroy', $job->uuid) }}" onsubmit="return confirm('Xác nhận xóa bỏ tác vụ này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--ghost" style="padding: 4px 8px; font-size: 0.75rem; color: #C0392B;" title="Xóa tác vụ">
                                                ✕
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($jobs->hasPages())
                <div style="padding: 16px 20px; border-top: 1px solid var(--mh-admin-border);">
                    {{ $jobs->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection

