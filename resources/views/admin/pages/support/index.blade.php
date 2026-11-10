@extends('admin.layouts.master')

@section('title', 'Trung Tâm Hỗ Trợ Khách Hàng — Mây Hẹn')
@section('page_title', 'Trung Tâm Hỗ Trợ Khách Hàng')

@section('content')
    {{-- KPI Overview Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Tổng Ticket
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                {{ number_format($kpis['total'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Toàn thời gian</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #E67E22;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Chờ Xử Lý
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #E67E22;">
                {{ number_format($kpis['open'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #E67E22; margin-top: 4px; font-weight: 600;">Trạng thái: Mới</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #2980B9;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Đang Xử Lý
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #2980B9;">
                {{ number_format($kpis['in_progress'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #2980B9; margin-top: 4px; font-weight: 600;">In Progress</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #C0392B;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Khẩn Cấp
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #C0392B;">
                {{ number_format($kpis['urgent'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #C0392B; margin-top: 4px; font-weight: 600;">Chưa giải quyết</div>
        </div>

        @if($kpis['avg_first_response_hours'] !== null)
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #27AE60;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                SLA Phản Hồi TB
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #27AE60;">
                {{ $kpis['avg_first_response_hours'] }}h
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Giờ phản hồi đầu tiên</div>
        </div>
        @endif
    </div>

    {{-- Bộ lọc & Tìm kiếm --}}
    <div class="mayhen_admin_card" style="margin-bottom: 20px;">
        <form method="GET" action="{{ route('admin.support.index') }}" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end;">
            <div style="flex: 1; min-width: 200px;">
                <label style="font-size: 0.75rem; font-weight: 600; color: var(--mh-admin-muted); display: block; margin-bottom: 4px;">TÌM KIẾM</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Mã ticket, tiêu đề..."
                       style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px; font-size: 0.875rem;">
            </div>

            <div style="min-width: 140px;">
                <label style="font-size: 0.75rem; font-weight: 600; color: var(--mh-admin-muted); display: block; margin-bottom: 4px;">TRẠNG THÁI</label>
                <select name="status" style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px; font-size: 0.875rem;">
                    <option value="">Tất cả</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Mới</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Đang xử lý</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Đã giải quyết</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Đã đóng</option>
                </select>
            </div>

            <div style="min-width: 140px;">
                <label style="font-size: 0.75rem; font-weight: 600; color: var(--mh-admin-muted); display: block; margin-bottom: 4px;">ƯU TIÊN</label>
                <select name="priority" style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px; font-size: 0.875rem;">
                    <option value="">Tất cả</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Khẩn cấp</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Cao</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Trung bình</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Thấp</option>
                </select>
            </div>

            <div style="min-width: 140px;">
                <label style="font-size: 0.75rem; font-weight: 600; color: var(--mh-admin-muted); display: block; margin-bottom: 4px;">PHÂN LOẠI</label>
                <select name="category" style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px; font-size: 0.875rem;">
                    <option value="">Tất cả</option>
                    <option value="technical" {{ request('category') === 'technical' ? 'selected' : '' }}>Kỹ thuật</option>
                    <option value="billing" {{ request('category') === 'billing' ? 'selected' : '' }}>Thanh toán</option>
                    <option value="invitation" {{ request('category') === 'invitation' ? 'selected' : '' }}>Thiệp cưới</option>
                    <option value="general" {{ request('category') === 'general' ? 'selected' : '' }}>Chung</option>
                </select>
            </div>

            <div style="min-width: 160px;">
                <label style="font-size: 0.75rem; font-weight: 600; color: var(--mh-admin-muted); display: block; margin-bottom: 4px;">PHỤ TRÁCH</label>
                <select name="assigned_to" style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px; font-size: 0.875rem;">
                    <option value="">Tất cả</option>
                    @foreach($staffList as $staff)
                        <option value="{{ $staff->id }}" {{ request('assigned_to') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="padding: 8px 16px;">Lọc</button>
                <a href="{{ route('admin.support.index') }}" class="mayhen_admin_btn mayhen_admin_btn--ghost" style="padding: 8px 16px;">Xoá lọc</a>
            </div>
        </form>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mayhen_admin_alert mayhen_admin_alert--success" style="margin-bottom: 16px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mayhen_admin_alert mayhen_admin_alert--danger" style="margin-bottom: 16px;">{{ session('error') }}</div>
    @endif

    {{-- Ticket Table --}}
    <div class="mayhen_admin_card" style="padding: 0; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--mh-admin-border); display: flex; align-items: center; justify-content: space-between;">
            <span style="font-weight: 700; font-size: 0.875rem;">{{ $tickets->total() }} ticket</span>
        </div>

        @if($tickets->isEmpty())
            <div style="padding: 48px; text-align: center; color: var(--mh-admin-muted);">
                <div style="font-size: 2rem; margin-bottom: 12px;">🎉</div>
                <div style="font-weight: 600;">Không có ticket nào.</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="mayhen_admin_table">
                    <thead>
                        <tr>
                            <th>Mã Ticket</th>
                            <th>Tiêu Đề</th>
                            <th>Khách Hàng</th>
                            <th>Phân Loại</th>
                            <th>Ưu Tiên</th>
                            <th>Trạng Thái</th>
                            <th>Phụ Trách</th>
                            <th>Ngày Tạo</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            <tr>
                                <td>
                                    <code style="font-size: 0.75rem; background: #f4f1ec; padding: 2px 6px; border-radius: 4px;">{{ $ticket->ticket_code }}</code>
                                </td>
                                <td style="max-width: 250px;">
                                    <a href="{{ route('admin.support.show', $ticket) }}"
                                       style="color: var(--mh-admin-charcoal); font-weight: 600; text-decoration: none;"
                                       title="{{ $ticket->subject }}">
                                        {{ Str::limit($ticket->subject, 60) }}
                                    </a>
                                </td>
                                <td style="font-size: 0.8rem;">{{ $ticket->user->name ?? '—' }}</td>
                                <td>
                                    <span class="mayhen_admin_badge mayhen_admin_badge--neutral">{{ $ticket->category_label }}</span>
                                </td>
                                <td>
                                    <span class="mayhen_admin_badge {{ $ticket->priority_badge_class }}">{{ $ticket->priority_label }}</span>
                                </td>
                                <td>
                                    <span class="mayhen_admin_badge {{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
                                </td>
                                <td style="font-size: 0.8rem;">{{ $ticket->assignedStaff->name ?? '—' }}</td>
                                <td style="font-size: 0.8rem; white-space: nowrap;">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.support.show', $ticket) }}"
                                       class="mayhen_admin_btn mayhen_admin_btn--ghost"
                                       style="padding: 4px 10px; font-size: 0.75rem;">Chi tiết</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Phân trang --}}
            @if($tickets->hasPages())
                <div style="padding: 16px 20px; border-top: 1px solid var(--mh-admin-border);">
                    {{ $tickets->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection

