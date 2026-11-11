@extends('admin.layouts.master')

@section('title', 'Nhật Ký Dời Lịch Cưới & Tái Xác Nhận RSVP')

@section('content')
<div class="mayhen_admin_reschedule_container" style="padding: 24px; max-width: 1400px; margin: 0 auto; font-family: var(--mh-admin-font, 'Open Sans', sans-serif);">

    <!-- Header Section -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <h1 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0; letter-spacing: -0.5px;">
                    Nhật Ký Dời Lịch Cưới &amp; Tái Xác Nhận RSVP
                </h1>
                <span style="font-size: 11px; font-weight: 700; background: #fee2e2; color: #b91c1c; padding: 3px 8px; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Luồng G • Mục 61 PRD V3
                </span>
            </div>
            <p style="font-size: 14px; color: #64748b; margin: 0;">
                Giám sát lịch sử đổi ngày cưới, địa điểm, quản lý luồng phát thông báo đính chính và chính sách yêu cầu quan khách tái xác nhận tham dự.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="button" onclick="openRescheduleModal()" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 8px; background: #1e293b; color: #ffffff; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                + Ghi Nhận Dời Lịch Cưới
            </button>
            <a href="{{ route('admin.reschedule.export', request()->all()) }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px; background: #ffffff; color: #334155; border: 1px solid #cbd5e1; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" x2="12" y1="15" y2="3"></line></svg>
                Xuất Tệp CSV
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; color: #065f46; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- 4 KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Tổng Lượt Dời Lịch Cưới
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #1e293b;">
                    {{ number_format($stats['total_reschedules']) }}
                </div>
                <span style="font-size: 12px; color: #64748b;">lượt thay đổi</span>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Khách Mời Bị Ảnh Hưởng
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #b91c1c;">
                    {{ number_format($stats['total_affected_guests']) }}
                </div>
                <span style="font-size: 12px; color: #b91c1c; font-weight: 600;">Quan khách</span>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Đã Phát Thông Báo Đính Chính
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #059669;">
                    {{ number_format($stats['total_notified_guests']) }}
                </div>
                <span style="font-size: 12px; color: #059669; font-weight: 600;">
                    {{ $stats['notification_rate'] }}% hoàn tất
                </span>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Yêu Cầu Tái Xác Nhận RSVP
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #d97706;">
                    {{ number_format($stats['reconfirm_requests_count']) }}
                </div>
                <span style="font-size: 12px; color: #64748b;">đợt reset RSVP</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px;">
        <form method="GET" action="{{ route('admin.reschedule.index') }}" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
            <div style="display: flex; gap: 12px; flex: 1; min-width: 280px; flex-wrap: wrap;">
                <div style="position: relative; flex: 1; min-width: 220px;">
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Tìm theo lý do, địa điểm, tên tiệc hoặc cặp đôi..." style="width: 100%; padding: 10px 14px 10px 38px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; box-sizing: border-box; outline: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 12px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" x2="16.65" y1="21" y2="16.65"></line></svg>
                </div>

                <select name="rsvp_action" style="padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; background: #ffffff; color: #334155; outline: none;">
                    <option value="all" {{ $filters['rsvp_action'] === 'all' ? 'selected' : '' }}>Tất cả chính sách RSVP</option>
                    <option value="keep_existing" {{ $filters['rsvp_action'] === 'keep_existing' ? 'selected' : '' }}>Giữ nguyên phản hồi cũ</option>
                    <option value="request_reconfirm" {{ $filters['rsvp_action'] === 'request_reconfirm' ? 'selected' : '' }}>Yêu cầu xác nhận lại</option>
                </select>

                <button type="submit" style="padding: 10px 20px; background: #1e293b; color: #ffffff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                    Lọc dữ liệu
                </button>

                @if($filters['q'] || $filters['rsvp_action'] !== 'all')
                    <a href="{{ route('admin.reschedule.index') }}" style="display: inline-flex; align-items: center; padding: 10px 14px; background: #f1f5f9; color: #475569; border-radius: 8px; font-size: 13px; text-decoration: none;">
                        Xóa lọc
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Main Table -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">
                        <th style="padding: 14px 20px;">Tiệc Cưới &amp; Cặp Đôi</th>
                        <th style="padding: 14px 20px;">Thay Đổi Ngày Cưới</th>
                        <th style="padding: 14px 20px;">Địa Điểm Tổ Chức</th>
                        <th style="padding: 14px 20px;">Lý Do &amp; Chính Sách RSVP</th>
                        <th style="padding: 14px 20px; text-align: center;">Tiến Độ Phát Thông Báo</th>
                        <th style="padding: 14px 20px; text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $rate = $log->affected_guests_count > 0 ? round(($log->notified_guests_count / $log->affected_guests_count) * 100) : 0;
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#fafaf9'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 16px 20px;">
                                <div style="font-weight: 600; color: #1e293b; font-size: 14px; margin-bottom: 4px;">
                                    {{ $log->wedding?->title ?: ($log->wedding?->groom_name . ' & ' . $log->wedding?->bride_name) }}
                                </div>
                                <div style="font-size: 12px; color: #64748b;">
                                    Chủ tiệc: <strong>{{ $log->wedding?->user?->name ?? 'Vô danh' }}</strong>
                                </div>
                            </td>

                            <td style="padding: 16px 20px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="color: #94a3b8; text-decoration: line-through; font-size: 12px;">
                                        {{ $log->old_date ? $log->old_date->format('d/m/Y') : 'Chưa đặt' }}
                                    </span>
                                    <span style="color: #64748b;">&rarr;</span>
                                    <span style="font-weight: 700; color: #047857; background: #ecfdf5; padding: 2px 8px; border-radius: 6px;">
                                        {{ $log->new_date->format('d/m/Y') }}
                                    </span>
                                </div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                    Thực hiện: {{ $log->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>

                            <td style="padding: 16px 20px;">
                                @if($log->old_venue !== $log->new_venue && $log->new_venue)
                                    <div style="font-size: 11px; color: #94a3b8; text-decoration: line-through;">{{ $log->old_venue ?: 'Chưa có' }}</div>
                                    <div style="font-weight: 600; color: #1e293b;">{{ $log->new_venue }}</div>
                                @else
                                    <div style="color: #334155;">{{ $log->new_venue ?: ($log->old_venue ?: 'Giữ nguyên') }}</div>
                                @endif
                            </td>

                            <td style="padding: 16px 20px;">
                                <div style="color: #334155; margin-bottom: 6px; font-style: italic; max-width: 260px;">
                                    "{{ $log->reason }}"
                                </div>
                                @if($log->rsvp_action === 'request_reconfirm')
                                    <span style="display: inline-block; font-size: 11px; font-weight: 700; background: #fef3c7; color: #b45309; padding: 2px 8px; border-radius: 10px;">
                                        Yêu cầu xác nhận lại RSVP
                                    </span>
                                @else
                                    <span style="display: inline-block; font-size: 11px; font-weight: 600; background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 10px;">
                                        Giữ nguyên phản hồi cũ
                                    </span>
                                @endif
                            </td>

                            <td style="padding: 16px 20px; text-align: center;">
                                <div style="font-weight: 700; color: #1e293b; font-size: 13px; margin-bottom: 4px;">
                                    {{ $log->notified_guests_count }} / {{ $log->affected_guests_count }} khách
                                </div>
                                <div style="width: 120px; height: 6px; background: #e2e8f0; border-radius: 3px; margin: 0 auto; overflow: hidden;">
                                    <div style="width: {{ $rate }}%; height: 100%; background: {{ $rate >= 100 ? '#10b981' : '#f59e0b' }}; border-radius: 3px;"></div>
                                </div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                    {{ $rate }}% đã nhận
                                </div>
                            </td>

                            <td style="padding: 16px 20px; text-align: right;">
                                <button type="button" onclick="openNotifyModal({{ $log->id }}, {{ $log->affected_guests_count }}, {{ $log->notified_guests_count }})" style="padding: 6px 12px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; color: #1e293b; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"></path></svg>
                                    Phát Thông Báo
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 40px; text-align: center; color: #64748b;">
                                Chưa có nhật ký đổi ngày cưới nào phù hợp với bộ lọc hiện tại.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0; background: #ffffff;">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<!-- MODAL: Ghi Nhận Dời Lịch Cưới Mới -->
<div id="reschedule-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 550px; width: 90%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); font-family: var(--mh-admin-font, 'Open Sans', sans-serif);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
            <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Ghi Nhận Dời Lịch Cưới Mới</h3>
            <button type="button" onclick="closeRescheduleModal()" style="border: none; background: transparent; font-size: 20px; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.reschedule.store') }}">
            @csrf
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Chọn Tiệc Cưới <span style="color: #ef4444;">*</span></label>
                <select name="wedding_id" id="modal-wedding-id" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit;">
                    <option value="">-- Chọn tiệc cưới cần dời ngày --</option>
                    @foreach($weddings as $w)
                        <option value="{{ $w->id }}" data-venue="{{ $w->venue_name }}" data-date="{{ $w->wedding_date ? $w->wedding_date->format('Y-m-d') : '' }}">
                            {{ $w->title ?: ($w->groom_name . ' & ' . $w->bride_name) }} (Ngày hiện tại: {{ $w->wedding_date ? $w->wedding_date->format('d/m/Y') : 'Chưa đặt' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Ngày Cưới Mới <span style="color: #ef4444;">*</span></label>
                    <input type="date" name="new_date" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Địa Điểm Mới</label>
                    <input type="text" name="new_venue" id="modal-new-venue" placeholder="Tên sảnh / khách sạn" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; box-sizing: border-box;">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Lý Do Dời Ngày Cưới <span style="color: #ef4444;">*</span></label>
                <textarea name="reason" rows="3" required placeholder="Ví dụ: Gia đình đổi ngày cưới sang cuối năm theo lời khuyên của gia tiên..." style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; box-sizing: border-box;"></textarea>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Chính Sách Xử Lý RSVP <span style="color: #ef4444;">*</span></label>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 13px; color: #334155; cursor: pointer;">
                        <input type="radio" name="rsvp_action" value="keep_existing" checked style="margin-top: 3px;">
                        <div>
                            <strong>Giữ nguyên phản hồi cũ</strong>
                            <div style="font-size: 11px; color: #64748b;">Khách đã xác nhận tham dự vẫn được giữ nguyên danh sách.</div>
                        </div>
                    </label>
                    <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 13px; color: #334155; cursor: pointer;">
                        <input type="radio" name="rsvp_action" value="request_reconfirm" style="margin-top: 3px;">
                        <div>
                            <strong style="color: #b45309;">Yêu cầu quan khách xác nhận lại (Re-confirm)</strong>
                            <div style="font-size: 11px; color: #64748b;">Đánh dấu cần xác nhận lại cho tất cả khách để cập nhật lịch trình mới.</div>
                        </div>
                    </label>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeRescheduleModal()" style="padding: 10px 16px; background: #f1f5f9; color: #475569; border: none; border-radius: 8px; font-size: 13px; cursor: pointer;">Hủy bỏ</button>
                <button type="submit" style="padding: 10px 20px; background: #1e293b; color: #ffffff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">Xác Nhận Dời Ngày</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Phát Thông Báo Đính Chính -->
<div id="notify-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 480px; width: 90%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); font-family: var(--mh-admin-font, 'Open Sans', sans-serif);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
            <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Phát Thông Báo Đính Chính</h3>
            <button type="button" onclick="closeNotifyModal()" style="border: none; background: transparent; font-size: 20px; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>

        <form id="notify-form" onsubmit="submitNotifyForm(event)">
            <input type="hidden" id="notify-log-id" value="">
            <div style="font-size: 13px; color: #334155; margin-bottom: 16px; line-height: 1.6;">
                Tiệc cưới này có tổng cộng <strong id="notify-affected-text">0</strong> khách mời trong danh bạ. Bạn muốn gửi thông báo đính chính ngày cưới mới tới bao nhiêu khách?
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Số Khách Nhận Thông Báo</label>
                <input type="number" id="notify-count-input" min="1" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; box-sizing: border-box;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeNotifyModal()" style="padding: 10px 16px; background: #f1f5f9; color: #475569; border: none; border-radius: 8px; font-size: 13px; cursor: pointer;">Đóng</button>
                <button type="submit" id="btn-submit-notify" style="padding: 10px 20px; background: #059669; color: #ffffff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">Phát Thông Báo Ngay</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRescheduleModal() {
    const modal = document.getElementById('reschedule-modal');
    if (modal) modal.style.display = 'flex';
}

function closeRescheduleModal() {
    const modal = document.getElementById('reschedule-modal');
    if (modal) modal.style.display = 'none';
}

function openNotifyModal(logId, affected, notified) {
    document.getElementById('notify-log-id').value = logId;
    document.getElementById('notify-affected-text').textContent = affected;
    document.getElementById('notify-count-input').value = affected;
    document.getElementById('notify-count-input').max = affected;

    const modal = document.getElementById('notify-modal');
    if (modal) modal.style.display = 'flex';
}

function closeNotifyModal() {
    const modal = document.getElementById('notify-modal');
    if (modal) modal.style.display = 'none';
}

function submitNotifyForm(e) {
    e.preventDefault();
    const logId = document.getElementById('notify-log-id').value;
    const count = document.getElementById('notify-count-input').value;
    const btn = document.getElementById('btn-submit-notify');

    btn.disabled = true;
    btn.textContent = 'Đang xử lý...';

    fetch('/admin/reschedule/' + logId + '/notify', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ notified_count: count })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = 'Phát Thông Báo Ngay';
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.textContent = 'Phát Thông Báo Ngay';
        console.error(err);
        alert('Lỗi kết nối máy chủ.');
    });
}

// Auto fill venue when select wedding
document.getElementById('modal-wedding-id')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const venue = opt.getAttribute('data-venue');
    if (venue) {
        document.getElementById('modal-new-venue').value = venue;
    }
});
</script>
@endsection

