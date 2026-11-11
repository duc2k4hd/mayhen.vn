@extends('admin.layouts.master')

@section('title', 'Báo Cáo Phản Hồi RSVP Quan Khách')
@section('page_title', 'Báo Cáo & Quản Lý Phản Hồi RSVP')

@section('breadcrumb_parent')
    <a href="{{ route('admin.wishes.index') }}">Lời Chúc & RSVP</a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/wishes.css') }}">
@endpush

@section('content')
    <!-- KPI Overview Cards -->
    <div class="mayhen_admin_grid_cards" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="display: flex; align-items: center; gap: 16px; padding: 20px;">
            <div class="mayhen_admin_kpi_icon" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background-color: #F0F4F8; color: #1E3A8A; border: 1px solid #DBEAFE;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--mh-admin-charcoal);">{{ number_format($totalRsvps) }}</div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Tổng Phản Hồi RSVP</div>
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
                <div style="font-size: 1.5rem; font-weight: 700; color: #1B5E20;">{{ number_format($attendingCount) }}</div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Sẽ Tham Dự ({{ $totalRsvps > 0 ? round(($attendingCount / $totalRsvps) * 100) : 0 }}%)</div>
            </div>
        </div>

        <div class="mayhen_admin_card" style="display: flex; align-items: center; gap: 16px; padding: 20px;">
            <div class="mayhen_admin_kpi_icon" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background-color: #FFEBEE; color: #C62828; border: 1px solid #FFCDD2;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #C62828;">{{ number_format($declinedCount) }}</div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Rất Tiếc Không Đến ({{ $totalRsvps > 0 ? round(($declinedCount / $totalRsvps) * 100) : 0 }}%)</div>
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
                <div style="font-size: 1.5rem; font-weight: 700; color: #B78103;">{{ number_format($uncertainCount) }}</div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Chưa Chắc Chắn</div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs: Wishes vs RSVPs -->
    <div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid var(--mh-admin-border); padding-bottom: 8px;">
        <a href="{{ route('admin.wishes.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <span>Sổ Lưu Bút & Lời Chúc</span>
        </a>
        <a href="{{ route('admin.wishes.rsvps') }}" class="mayhen_admin_btn" style="background-color: var(--mh-admin-charcoal); color: #FFF; border-color: var(--mh-admin-charcoal);">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 11l3 3L22 4"></path>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
            <span>Phản Hồi RSVP Quan Khách ({{ number_format($totalRsvps) }})</span>
        </a>
    </div>

    <!-- Filter Bar -->
    <form action="{{ route('admin.wishes.rsvps') }}" method="GET" class="mayhen_admin_filter_bar" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 24px;">
        <div class="mayhen_admin_search_box" style="flex: 1; min-width: 260px; position: relative;">
            <input 
                type="text" 
                name="q" 
                value="{{ $currentSearch }}" 
                placeholder="Tìm tên khách mời, số điện thoại, lời nhắn ăn uống..." 
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
            <option value="attending" {{ $currentStatus === 'attending' ? 'selected' : '' }}>Sẽ tham dự</option>
            <option value="declined" {{ $currentStatus === 'declined' ? 'selected' : '' }}>Không tham dự</option>
            <option value="uncertain" {{ $currentStatus === 'uncertain' ? 'selected' : '' }}>Chưa chắc chắn</option>
        </select>

        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            <span>Lọc RSVP</span>
        </button>

        @if($currentSearch || $currentStatus)
            <a href="{{ route('admin.wishes.rsvps') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                <span>Đặt lại</span>
            </a>
        @endif
    </form>

    <!-- RSVP Report Table Card -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div class="mayhen_admin_card_title">Danh Sách Phản Hồi RSVP Quan Khách</div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Hiển thị {{ $rsvps->count() }} / {{ $rsvps->total() }} lượt phản hồi</div>
                <a href="{{ route('admin.wishes.rsvps.export', ['q' => $currentSearch, 'status' => $currentStatus]) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>Xuất CSV (Excel)</span>
                </a>
            </div>
        </div>

        <div class="mayhen_admin_table_wrapper">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th style="width: 180px;">Khách Mời</th>
                        <th style="width: 180px;">Thiệp Cưới / Cặp Đôi</th>
                        <th style="width: 140px; text-align: center;">Trạng Thái Tham Dự</th>
                        <th style="width: 130px; text-align: center;">Số Người Đi Cùng</th>
                        <th>Chế Độ Ăn & Lời Nhắn</th>
                        <th style="width: 140px; text-align: right;">Thời Gian Gửi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rsvps as $rsvp)
                        @php
                            $wedding = $rsvp->wedding;
                            $invitation = $wedding?->invitations?->first();
                        @endphp
                        <tr>
                            <!-- Guest Name & Phone -->
                            <td>
                                <div style="font-weight: 600; color: var(--mh-admin-charcoal);">{{ $rsvp->guest_name }}</div>
                                @if(!empty($rsvp->phone))
                                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted); font-family: monospace;">{{ $rsvp->phone }}</div>
                                @endif
                                @if(!empty($rsvp->ip_address))
                                    <div style="font-size: 0.6875rem; color: var(--mh-admin-muted); opacity: 0.7;">IP: {{ $rsvp->ip_address }}</div>
                                @endif
                            </td>

                            <!-- Wedding -->
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

                            <!-- Attending Status -->
                            <td style="text-align: center;">
                                @if($rsvp->attending_status === 'attending')
                                    <span class="mayhen_admin_rsvp_badge mayhen_admin_rsvp_yes">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        <span>Có Tham Dự</span>
                                    </span>
                                @elseif($rsvp->attending_status === 'declined')
                                    <span class="mayhen_admin_rsvp_badge mayhen_admin_rsvp_no">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                        <span>Không Đến Được</span>
                                    </span>
                                @else
                                    <span class="mayhen_admin_rsvp_badge mayhen_admin_rsvp_uncertain">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        <span>Chưa Chắc Chắn</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Companion Counts -->
                            <td style="text-align: center; font-size: 0.875rem;">
                                @php
                                    $adults = (int) ($rsvp->adults_count ?? 1);
                                    $children = (int) ($rsvp->children_count ?? 0);
                                @endphp
                                <div style="font-weight: 600; color: var(--mh-admin-charcoal);">
                                    {{ $adults }} người lớn
                                </div>
                                @if($children > 0)
                                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">+ {{ $children }} trẻ em</div>
                                @endif
                            </td>

                            <!-- Dietary Notes & Wishes -->
                            <td>
                                @if(!empty($rsvp->dietary_requirements))
                                    <div style="font-size: 0.8125rem; margin-bottom: 4px;">
                                        <span style="font-weight: 600; color: #B78103;">Ăn uống:</span>
                                        <span>{{ $rsvp->dietary_requirements }}</span>
                                    </div>
                                @endif
                                @if(!empty($rsvp->wishes))
                                    <div style="font-size: 0.8125rem; color: var(--mh-admin-charcoal); font-style: italic;">
                                        &ldquo;{{ $rsvp->wishes }}&rdquo;
                                    </div>
                                @endif
                                @if(empty($rsvp->dietary_requirements) && empty($rsvp->wishes))
                                    <span style="color: var(--mh-admin-muted); font-size: 0.8125rem;">— Không có ghi chú —</span>
                                @endif
                            </td>

                            <!-- Created At -->
                            <td style="text-align: right; font-size: 0.8125rem; color: var(--mh-admin-muted);">
                                {{ $rsvp->created_at ? $rsvp->created_at->format('d/m/Y H:i') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 48px; color: var(--mh-admin-muted);">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 12px; opacity: 0.5;">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                </svg>
                                <div>Chưa có lượt phản hồi RSVP nào phù hợp với bộ lọc tìm kiếm.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rsvps->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--mh-admin-border);">
                {{ $rsvps->links() }}
            </div>
        @endif
    </div>
@endsection

