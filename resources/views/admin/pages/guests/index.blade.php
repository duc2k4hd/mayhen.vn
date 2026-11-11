@extends('admin.layouts.master')

@section('title', 'Quản Lý Khách Mời & Điểm Danh')
@section('page_title', 'Quản Lý Khách Mời & Điểm Danh Ngày Cưới')

@section('content')
    <!-- KPI Overview Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Tổng Khách Mời
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                {{ number_format($totalGuests, 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">
                Đã đăng ký trong danh bạ
            </div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #2E7D32;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Đã Đón Tiếp (Check-in)
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #2E7D32;">
                {{ number_format($checkedInCount, 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #2E7D32; margin-top: 4px; font-weight: 600;">
                Tỷ lệ tiếp đón: {{ $checkInRate }}%
            </div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Phân Bổ Bàn Tiệc
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                {{ number_format($assignedCount, 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">
                Chưa xếp bàn: <strong style="color: #F57F17;">{{ $unassignedCount }}</strong> khách
            </div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid var(--mh-admin-gold);">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Xác Nhận RSVP
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--mh-admin-gold);">
                {{ number_format($attendingRsvps, 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">
                Trên tổng số {{ $totalRsvps }} phản hồi
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <form action="{{ route('admin.guests.index') }}" method="GET" class="mayhen_admin_filter_bar">
        <div class="mayhen_admin_search_box">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #7A756E;">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input 
                type="text" 
                name="q" 
                value="{{ $currentSearch }}" 
                placeholder="Tìm tên khách, số điện thoại, nhóm, bàn, cô dâu chú rể..." 
                class="mayhen_admin_search_input"
            >
        </div>

        <select name="status" class="mayhen_admin_select">
            <option value="">Tất Cả Trạng Thái</option>
            <option value="checked_in" {{ $currentStatus === 'checked_in' ? 'selected' : '' }}>Đã Điểm Danh (Check-in)</option>
            <option value="not_checked_in" {{ $currentStatus === 'not_checked_in' ? 'selected' : '' }}>Chưa Điểm Danh</option>
            <option value="assigned" {{ $currentStatus === 'assigned' ? 'selected' : '' }}>Đã Xếp Bàn Tiệc</option>
            <option value="unassigned" {{ $currentStatus === 'unassigned' ? 'selected' : '' }}>Chưa Xếp Bàn</option>
        </select>

        @if ($allWeddings->isNotEmpty())
            <select name="wedding_id" class="mayhen_admin_select" style="max-width: 200px;">
                <option value="">Tất Cả Lễ Cưới</option>
                @foreach ($allWeddings as $w)
                    <option value="{{ $w->id }}" {{ (string)$currentWeddingId === (string)$w->id ? 'selected' : '' }}>
                        {{ $w->groom_name }} & {{ $w->bride_name }}
                    </option>
                @endforeach
            </select>
        @endif

        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">
            <span>Tìm Kiếm</span>
        </button>

        @if ($currentSearch || $currentStatus || $currentWeddingId)
            <a href="{{ route('admin.guests.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                <span>Đặt Lại</span>
            </a>
        @endif
    </form>

    <!-- Guests Table Card -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div class="mayhen_admin_card_title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Danh Sách Quan Khách ({{ $guests->total() }} khách)</span>
            </div>

            <div style="display: flex; gap: 8px; align-items: center;">
                <a href="{{ route('admin.guests.import.index', ['wedding_id' => $currentWeddingId]) }}" class="mayhen_admin_btn mayhen_admin_btn_primary mayhen_admin_btn_sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <span>Nhập Hàng Loạt (Batch)</span>
                </a>
                <a href="{{ route('admin.guests.export', ['q' => $currentSearch, 'status' => $currentStatus, 'wedding_id' => $currentWeddingId]) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>Xuất CSV</span>
                </a>
            </div>
        </div>

        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th>Tên Quan Khách</th>
                        <th>Lễ Cưới Cặp Đôi</th>
                        <th>Nhóm Khách</th>
                        <th>Bàn Tiệc</th>
                        <th>Xác Nhận RSVP</th>
                        <th>Sĩ Số</th>
                        <th>Trạng Thái Check-in</th>
                        <th style="text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($guests as $guest)
                        <tr>
                            <td>
                                <strong>{{ $guest->name }}</strong><br>
                                <span style="font-size: 0.75rem; color: #7A756E;">{{ $guest->phone ?? 'Chưa có SĐT' }}</span>
                            </td>

                            <td>
                                @if ($guest->wedding)
                                    <strong>{{ $guest->wedding->groom_name }} & {{ $guest->wedding->bride_name }}</strong>
                                    @if ($guest->wedding->invitations->isNotEmpty())
                                        <br>
                                        <a href="{{ route('admin.invitations.show', $guest->wedding->invitations->first()->id) }}" style="font-size: 0.75rem; color: var(--mh-admin-gold); text-decoration: underline;">
                                            Xem Thiệp &nearr;
                                        </a>
                                    @endif
                                @else
                                    <span style="color: #7A756E;">N/A</span>
                                @endif
                            </td>

                            <td>
                                <span class="mayhen_admin_badge mayhen_admin_badge_muted">
                                    {{ $guest->group_name ?? 'Chung' }}
                                </span>
                            </td>

                            <td>
                                @php $tableName = $guest->table?->name ?? $guest->table_name; @endphp
                                @if ($tableName)
                                    <span class="mayhen_admin_badge mayhen_admin_badge_gold">
                                        {{ $tableName }}
                                    </span>
                                @else
                                    <span style="font-size: 0.75rem; color: #9C948A;">Chưa xếp</span>
                                @endif
                            </td>

                            <td>
                                @if ($guest->response)
                                    @if ($guest->response->attending_status === 'attending')
                                        <span class="mayhen_admin_badge mayhen_admin_badge_success">Có Tham Dự</span>
                                    @elseif ($guest->response->attending_status === 'declined')
                                        <span class="mayhen_admin_badge mayhen_admin_badge_danger">Không Tham Dự</span>
                                    @else
                                        <span class="mayhen_admin_badge mayhen_admin_badge_warning">Chưa Chắc Chắn</span>
                                    @endif
                                @else
                                    <span style="font-size: 0.75rem; color: #7A756E;">Chưa gửi</span>
                                @endif
                            </td>

                            <td>
                                <strong>{{ $guest->actualAttendeesCount() }}</strong> người
                            </td>

                            <td>
                                @if ($guest->isCheckedIn())
                                    <span class="mayhen_admin_badge mayhen_admin_badge_success">
                                        ✓ Đã Đón ({{ $guest->checked_in_attendees }} người)
                                    </span>
                                    <div style="font-size: 0.6875rem; color: #7A756E; margin-top: 2px;">
                                        {{ $guest->checked_in_at->format('d/m H:i') }}
                                    </div>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge_muted">Chưa Đến</span>
                                @endif
                            </td>

                            <td style="text-align: right;">
                                <form action="{{ route('admin.guests.toggle_checkin', $guest->id) }}" method="POST" style="display: inline-block; margin: 0;">
                                    @csrf
                                    <button 
                                        type="submit" 
                                        class="mayhen_admin_btn mayhen_admin_btn_sm {{ $guest->isCheckedIn() ? 'mayhen_admin_btn_outline' : 'mayhen_admin_btn_primary' }}"
                                        title="{{ $guest->isCheckedIn() ? 'Hủy điểm danh' : 'Điểm danh đón tiếp' }}"
                                    >
                                        {{ $guest->isCheckedIn() ? 'Hủy Check-in' : 'Điểm Danh' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: #7A756E; padding: 40px;">
                                Không tìm thấy quan khách nào phù hợp với điều kiện lọc.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($guests->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--mh-admin-border);">
                {{ $guests->links() }}
            </div>
        @endif
    </div>
@endsection

