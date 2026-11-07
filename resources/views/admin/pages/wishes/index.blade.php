@extends('admin.layouts.master')

@section('title', 'Quản Lý Lời Chúc & Sổ Lưu Bút — Mây Hẹn')
@section('page_title', 'Lời Chúc & Kiểm Duyệt Sổ Lưu Bút')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/wishes.css') }}">
@endpush

@section('content')
    <!-- KPI Overview Cards -->
    <div class="mayhen_admin_grid_cards" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="display: flex; align-items: center; gap: 16px; padding: 20px;">
            <div class="mayhen_admin_kpi_icon" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background-color: #FBF7EE; color: var(--mh-admin-gold); border: 1px solid #EFE4CA;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--mh-admin-charcoal);">{{ number_format($totalWishes) }}</div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Tổng Lời Chúc Toàn Hệ Thống</div>
            </div>
        </div>

        <div class="mayhen_admin_card" style="display: flex; align-items: center; gap: 16px; padding: 20px;">
            <div class="mayhen_admin_kpi_icon" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background-color: #E8F5E9; color: #1B5E20; border: 1px solid #C8E6C9;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #1B5E20;">{{ number_format($approvedCount) }}</div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Đã Duyệt Hiển Thị</div>
            </div>
        </div>

        <div class="mayhen_admin_card" style="display: flex; align-items: center; gap: 16px; padding: 20px;">
            <div class="mayhen_admin_kpi_icon" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background-color: #FFF8E1; color: #B78103; border: 1px solid #FFE082;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #B78103;">{{ number_format($pendingCount) }}</div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Chờ Duyệt / Tạm Ẩn</div>
            </div>
        </div>

        <div class="mayhen_admin_card" style="display: flex; align-items: center; gap: 16px; padding: 20px;">
            <div class="mayhen_admin_kpi_icon" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background-color: #E3F2FD; color: #0D47A1; border: 1px solid #BBDEFB;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0D47A1;">{{ $attendanceRate }}%</div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Tỷ Lệ Tham Dự RSVP ({{ $attendingCount }}/{{ $totalRsvps }})</div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs: Wishes vs RSVPs -->
    <div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid var(--mh-admin-border); padding-bottom: 8px;">
        <a href="{{ route('admin.wishes.index') }}" class="mayhen_admin_btn" style="background-color: var(--mh-admin-charcoal); color: #FFF; border-color: var(--mh-admin-charcoal);">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <span>Sổ Lưu Bút & Lời Chúc ({{ number_format($totalWishes) }})</span>
        </a>
        <a href="{{ route('admin.wishes.rsvps') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 11l3 3L22 4"></path>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
            <span>Phản Hồi RSVP Quan Khách ({{ number_format($totalRsvps) }})</span>
        </a>
    </div>

    <!-- Filter Bar -->
    <form action="{{ route('admin.wishes.index') }}" method="GET" class="mayhen_admin_filter_bar" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 24px;">
        <div class="mayhen_admin_search_box" style="flex: 1; min-width: 260px; position: relative;">
            <input 
                type="text" 
                name="q" 
                value="{{ $currentSearch }}" 
                placeholder="Tìm tên người gửi, nội dung lời chúc, tên cặp đôi..." 
                class="mayhen_admin_input"
                style="width: 100%; padding-left: 36px;"
            >
            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--mh-admin-muted);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </span>
        </div>

        <select name="status" class="mayhen_admin_select" style="min-width: 170px;">
            <option value="">Tất cả trạng thái</option>
            <option value="approved" {{ $currentStatus === 'approved' ? 'selected' : '' }}>Đã duyệt hiển thị</option>
            <option value="pending" {{ $currentStatus === 'pending' ? 'selected' : '' }}>Chờ duyệt / Ẩn</option>
            <option value="flagged" {{ $currentStatus === 'flagged' ? 'selected' : '' }}>🚩 Nghi vấn spam / Nhạy cảm</option>
        </select>

        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            <span>Lọc Lời Chúc</span>
        </button>

        @if($currentSearch || $currentStatus || $currentWeddingId)
            <a href="{{ route('admin.wishes.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                <span>Đặt lại</span>
            </a>
        @endif
    </form>

    <!-- Wishes List Table Card -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="mayhen_admin_card_title">Danh Sách Lời Chúc Mừng Khách Mời</div>
            <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Hiển thị {{ $wishes->count() }} / {{ $wishes->total() }} lời chúc</div>
        </div>

        <div class="mayhen_admin_table_wrapper">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th style="width: 160px;">Người Gửi</th>
                        <th style="width: 200px;">Thiệp Cưới / Cặp Đôi</th>
                        <th>Nội Dung Lời Chúc</th>
                        <th style="width: 130px; text-align: center;">Trạng Thái</th>
                        <th style="width: 140px;">Thời Gian</th>
                        <th style="width: 130px; text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wishes as $wish)
                        @php
                            $msgLower = mb_strtolower($wish->message);
                            $detectedFlags = [];
                            foreach ($spamKeywords as $kw) {
                                if (str_contains($msgLower, mb_strtolower($kw))) {
                                    $detectedFlags[] = $kw;
                                }
                            }
                            $isFlagged = count($detectedFlags) > 0;
                            $wedding = $wish->wedding;
                            $invitation = $wedding?->invitations?->first();
                        @endphp
                        <tr>
                            <!-- Sender -->
                            <td>
                                <div style="font-weight: 600; color: var(--mh-admin-charcoal);">{{ $wish->sender_name }}</div>
                                @if(!empty($wish->ip_address))
                                    <div style="font-size: 0.6875rem; color: var(--mh-admin-muted); font-family: monospace;">IP: {{ $wish->ip_address }}</div>
                                @endif
                            </td>

                            <!-- Wedding / Couple -->
                            <td>
                                @if($wedding)
                                    <div style="font-weight: 600; font-size: 0.875rem; color: var(--mh-admin-charcoal);">
                                        {{ $wedding->groom_name }} & {{ $wedding->bride_name }}
                                    </div>
                                    @if($invitation)
                                        <a href="{{ route('admin.invitations.show', $invitation->id) }}" style="font-size: 0.75rem; color: var(--mh-admin-gold); text-decoration: none;" target="_blank">
                                            Xem chi tiết thiệp &rarr;
                                        </a>
                                    @endif
                                @else
                                    <span style="color: var(--mh-admin-muted); font-size: 0.8125rem;">(Thiệp đã xóa)</span>
                                @endif
                            </td>

                            <!-- Wish Message Bubble -->
                            <td>
                                <div class="mayhen_admin_wish_bubble {{ $isFlagged ? 'flagged' : '' }}">
                                    @if($isFlagged)
                                        <div class="mayhen_admin_spam_flag">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                                                <line x1="4" y1="22" x2="4" y2="15"></line>
                                            </svg>
                                            <span>Nghi vấn từ khóa nhạy cảm: {{ implode(', ', $detectedFlags) }}</span>
                                        </div>
                                    @endif
                                    <div>{{ $wish->message }}</div>
                                </div>
                            </td>

                            <!-- Approval Status -->
                            <td style="text-align: center;">
                                @if($wish->is_approved)
                                    <span class="mayhen_admin_badge mayhen_admin_badge_success">Đã duyệt</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge_warning">Chờ duyệt / Ẩn</span>
                                @endif
                            </td>

                            <!-- Sent Time -->
                            <td style="font-size: 0.8125rem; color: var(--mh-admin-muted);">
                                {{ $wish->created_at ? $wish->created_at->format('d/m/Y H:i') : '—' }}
                            </td>

                            <!-- Actions -->
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                                    <!-- Toggle Approval Button -->
                                    <form action="{{ route('admin.wishes.toggle_approval', $wish->id) }}" method="POST">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="mayhen_admin_btn mayhen_admin_btn_sm {{ $wish->is_approved ? 'mayhen_admin_btn_outline' : 'mayhen_admin_btn_primary' }}"
                                            title="{{ $wish->is_approved ? 'Tạm ẩn lời chúc này' : 'Duyệt hiển thị lời chúc này' }}"
                                        >
                                            @if($wish->is_approved)
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                                </svg>
                                                <span>Ẩn</span>
                                            @else
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                                <span>Duyệt</span>
                                            @endif
                                        </button>
                                    </form>

                                    <!-- Delete Button -->
                                    <form action="{{ route('admin.wishes.destroy', $wish->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn lời chúc từ {{ addslashes($wish->sender_name) }} không?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_sm" style="color: var(--mh-admin-danger-text); background-color: var(--mh-admin-danger-bg); border-color: #FFCDD2;" title="Xóa vĩnh viễn">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 48px; color: var(--mh-admin-muted);">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 12px; opacity: 0.5;">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <div>Chưa có lời chúc nào phù hợp với bộ lọc tìm kiếm.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($wishes->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--mh-admin-border);">
                {{ $wishes->links() }}
            </div>
        @endif
    </div>
@endsection

