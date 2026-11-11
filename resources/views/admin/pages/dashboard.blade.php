@extends('admin.layouts.master')

@section('title', 'Admin Dashboard')
@section('page_title', 'Tổng Quan Vận Hành & Doanh Thu')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/dashboard.css') }}">
@endpush

@section('content')
    <!-- Role-Aware Morning Briefing Banner (Innovation #18) -->
    @if (isset($morningBrief))
        <div class="mayhen_admin_card mayhen_admin_morning_brief" style="background: linear-gradient(135deg, #FFFDF9 0%, #FAF6EE 100%); border-left: 4px solid var(--mh-admin-gold); padding: 20px 24px; margin-bottom: 24px; box-shadow: 0 4px 16px rgba(197, 168, 128, 0.08);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                        <span style="font-size: 1.25rem;">✨</span>
                        <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                            {{ $morningBrief['greeting'] }}
                        </h2>
                        <span class="mayhen_admin_badge mayhen_admin_badge_info" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            {{ $morningBrief['role_title'] }}
                        </span>
                    </div>
                    <p style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin: 0;">
                        {{ $morningBrief['date_str'] }} &bull; Điểm tin tóm tắt vận hành Atelier Command Center dành riêng cho bạn
                    </p>
                </div>

                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <a href="{{ route('admin.guests.rescue') }}" class="mayhen_admin_btn mayhen_admin_btn--primary mayhen_admin_btn_sm">
                        🚨 Rescue Cockpit Ngày Cưới &rarr;
                    </a>
                    <a href="{{ route('admin.intelligence.inbox') }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                        📥 Action Inbox &rarr;
                    </a>
                </div>
            </div>

            <!-- Role-Specific Operational Metrics Highlight -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; background: rgba(255,255,255,0.7); border-radius: 8px; padding: 14px; border: 1px solid rgba(229, 220, 205, 0.6);">
                @if (in_array($morningBrief['role'], ['admin', 'manager']))
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.2rem;">💰</span>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">Doanh thu 24h qua</div>
                            <strong style="font-size: 0.95rem; color: #1B5E20;">{{ number_format($morningBrief['revenue_24h'], 0, ',', '.') }}đ</strong>
                            <span style="font-size: 0.7rem; color: var(--mh-admin-muted);">({{ $morningBrief['orders_24h'] }} đơn mới)</span>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.2rem;">⚡</span>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">Đơn hàng chờ duyệt (P0)</div>
                            <strong style="font-size: 0.95rem; color: {{ $morningBrief['pending_orders_count'] > 0 ? '#C62828' : 'var(--mh-admin-charcoal)' }};">
                                {{ $morningBrief['pending_orders_count'] }} đơn
                            </strong>
                        </div>
                    </div>
                @else
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.2rem;">🎧</span>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">Ticket hỗ trợ mở</div>
                            <strong style="font-size: 0.95rem; color: {{ $morningBrief['open_tickets_count'] > 0 ? '#E65100' : 'var(--mh-admin-charcoal)' }};">
                                {{ $morningBrief['open_tickets_count'] }} yêu cầu
                            </strong>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.2rem;">💌</span>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">Lời chúc chờ duyệt</div>
                            <strong style="font-size: 0.95rem; color: var(--mh-admin-charcoal);">{{ $morningBrief['pending_wishes_count'] }} lời chúc</strong>
                        </div>
                    </div>
                @endif

                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.2rem;">💍</span>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">Đám cưới HÔM NAY</div>
                        <strong style="font-size: 0.95rem; color: {{ $morningBrief['today_weddings_count'] > 0 ? '#0D47A1' : 'var(--mh-admin-charcoal)' }};">
                            {{ $morningBrief['today_weddings_count'] }} đám cưới
                        </strong>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.2rem;">📡</span>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">Radar Rủi ro (<14 ngày)</div>
                        <strong style="font-size: 0.95rem; color: {{ $morningBrief['radar_high_risk_count'] > 0 ? '#C62828' : '#2E7D32' }};">
                            {{ $morningBrief['radar_high_risk_count'] }} ca cần xử lý
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Operational Actionable Alerts Banner -->
    @if (($pendingOrdersCount ?? 0) > 0 || ($pendingWishesCount ?? 0) > 0 || ($lockedInvitationsCount ?? 0) > 0)
        <div class="mayhen_admin_card" style="background: #FFFDF9; border-left: 4px solid var(--mh-admin-gold); padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #FAF3E0; color: #9C7A4A; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <div>
                    <strong style="font-size: 0.9375rem; color: var(--mh-admin-charcoal);">Cảnh Báo Vận Hành Cần Xử Lý</strong>
                    <div style="font-size: 0.8125rem; color: var(--mh-admin-muted); display: flex; gap: 16px; flex-wrap: wrap; margin-top: 2px;">
                        @if (($pendingOrdersCount ?? 0) > 0)
                            <span>&bull; <strong>{{ $pendingOrdersCount }}</strong> đơn hàng chờ thanh toán</span>
                        @endif
                        @if (($pendingWishesCount ?? 0) > 0)
                            <span>&bull; <strong>{{ $pendingWishesCount }}</strong> lời chúc chờ duyệt / tạm ẩn</span>
                        @endif
                        @if (($lockedInvitationsCount ?? 0) > 0)
                            <span>&bull; <strong>{{ $lockedInvitationsCount }}</strong> thiệp cưới đang bị khóa</span>
                        @endif
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="{{ route('admin.intelligence.inbox') }}" class="mayhen_admin_btn mayhen_admin_btn--primary mayhen_admin_btn_sm">
                    📥 Mở Action Inbox &rarr;
                </a>
                @if (($pendingOrdersCount ?? 0) > 0)
                    <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                        Xem Đơn Chờ &rarr;
                    </a>
                @endif
                @if (($pendingWishesCount ?? 0) > 0)
                    <a href="{{ route('admin.wishes.index', ['status' => 'pending']) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                        Duyệt Lời Chúc &rarr;
                    </a>
                @endif
                @if (($lockedInvitationsCount ?? 0) > 0)
                    <a href="{{ route('admin.invitations.index', ['status' => 'locked']) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                        Xử Lý Thiệp Khóa &rarr;
                    </a>
                @endif
            </div>
        </div>
    @endif

    <!-- 5 Key KPI Metrics -->
    <div class="mayhen_admin_grid_cards" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="display: flex; align-items: center; gap: 16px; padding: 20px;">
            <div class="mayhen_admin_kpi_icon" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background-color: #E8F5E9; color: #1B5E20; border: 1px solid #C8E6C9;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div>
                <div style="font-size: 1.4rem; font-weight: 700; color: #1B5E20;">{{ number_format($totalRevenue, 0, ',', '.') }}đ</div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Doanh Thu Đã Thu</div>
            </div>
        </div>

        <div class="mayhen_admin_card" style="display: flex; align-items: center; gap: 16px; padding: 20px;">
            <div class="mayhen_admin_kpi_icon" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background-color: #FBF7EE; color: var(--mh-admin-gold); border: 1px solid #EFE4CA;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div>
                <div style="font-size: 1.4rem; font-weight: 700; color: var(--mh-admin-charcoal);">{{ $paidOrdersCount }}</div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Đơn Hàng Thành Công</div>
            </div>
        </div>

        <div class="mayhen_admin_card" style="display: flex; align-items: center; gap: 16px; padding: 20px;">
            <div class="mayhen_admin_kpi_icon" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background-color: #E0F2FE; color: #0369A1; border: 1px solid #BAE6FD;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
            </div>
            <div>
                <div style="font-size: 1.4rem; font-weight: 700; color: var(--mh-admin-charcoal);">{{ $publishedInvitations }} <span style="font-size: 0.875rem; font-weight: 500; color: var(--mh-admin-muted);">/ {{ $totalInvitations }}</span></div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Thiệp Xuất Bản / Tổng Thiệp</div>
            </div>
        </div>

        <div class="mayhen_admin_card" style="display: flex; align-items: center; gap: 16px; padding: 20px;">
            <div class="mayhen_admin_kpi_icon" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background-color: #EDE9FE; color: #6D28D9; border: 1px solid #DDD6FE;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div>
                <div style="font-size: 1.4rem; font-weight: 700; color: var(--mh-admin-charcoal);">{{ number_format($totalGuests) }}</div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Khách Mời & RSVP</div>
            </div>
        </div>

        <div class="mayhen_admin_card" style="display: flex; align-items: center; gap: 16px; padding: 20px;">
            <div class="mayhen_admin_kpi_icon" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background-color: #F1F5F9; color: #475569; border: 1px solid #E2E8F0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <div>
                <div style="font-size: 1.4rem; font-weight: 700; color: var(--mh-admin-charcoal);">{{ number_format($totalUsers) }}</div>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Tổng Người Dùng</div>
            </div>
        </div>
    </div>

    <!-- 2-Column Split: Revenue Chart & Quick Actions -->
    <div class="mayhen_admin_dashboard_split">
        <!-- Left: Revenue Trend Chart -->
        <div class="mayhen_admin_card" style="padding: 24px; margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                        Xu Hướng Doanh Thu 7 Ngày Gần Nhất
                    </h3>
                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 2px;">
                        Thống kê số tiền thanh toán thực nhận theo từng ngày
                    </div>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                    Chi tiết &rarr;
                </a>
            </div>

            <!-- Native Flexbox Chart -->
            <div class="mayhen_admin_revenue_chart">
                @foreach ($revenueLast7Days as $idx => $dayData)
                    @php
                        $pct = $maxDailyRevenue > 0 ? round(($dayData['amount'] / $maxDailyRevenue) * 100) : 0;
                        $isToday = $idx === count($revenueLast7Days) - 1;
                    @endphp
                    <div class="mayhen_admin_chart_col">
                        <div class="mayhen_admin_chart_tooltip">
                            {{ $dayData['day'] }}: {{ number_format($dayData['amount'], 0, ',', '.') }}đ
                        </div>
                        <div class="mayhen_admin_chart_bar {{ $isToday ? 'today' : '' }}" style="height: {{ max($pct, 6) }}%;"></div>
                        <div class="mayhen_admin_chart_label">{{ $dayData['day_name'] }}</div>
                    </div>
                @endforeach
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; color: var(--mh-admin-muted); padding-top: 4px;">
                <div>Cột xanh: Hôm nay</div>
                <div>Cột vàng: Các ngày trước</div>
            </div>
        </div>

        <!-- Right: Quick Actions -->
        <div class="mayhen_admin_card" style="padding: 24px; margin-bottom: 0;">
            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0 0 16px 0;">
                Lối Tắt Vận Hành Nhanh
            </h3>

            <div class="mayhen_admin_quick_actions">
                <a href="{{ route('admin.intelligence.inbox') }}" class="mayhen_admin_quick_btn" style="background: #FDFBF7; border-color: var(--mh-admin-gold);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline>
                        <path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path>
                    </svg>
                    <span>Action Inbox</span>
                </a>

                @if (Auth::user() && Auth::user()->isSuperAdmin())
                    <form action="{{ route('admin.templates.scan_discovery') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="mayhen_admin_quick_btn" style="width: 100%; border: 1px solid var(--mh-admin-border); cursor: pointer;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                            </svg>
                            <span>Quét Mẫu Mới</span>
                        </button>
                    </form>

                    <a href="{{ route('admin.wishes.index') }}" class="mayhen_admin_quick_btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <span>Duyệt Lời Chúc</span>
                    </a>

                    <a href="{{ route('admin.settings.banking') }}" class="mayhen_admin_quick_btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                            <line x1="2" y1="10" x2="22" y2="10"></line>
                        </svg>
                        <span>Cài Đặt VietQR</span>
                    </a>

                    <a href="{{ route('admin.settings.storage') }}" class="mayhen_admin_quick_btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                            <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                            <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                        </svg>
                        <span>Bộ Nhớ Lưu Trữ</span>
                    </a>
                @else
                    <a href="{{ route('admin.invitations.index') }}" class="mayhen_admin_quick_btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <span>Quản Lý Thiệp</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}" class="mayhen_admin_quick_btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                        <span>Kiểm Tra Đơn</span>
                    </a>

                    <a href="{{ route('admin.wishes.index') }}" class="mayhen_admin_quick_btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <span>Duyệt Lời Chúc</span>
                    </a>

                    <a href="{{ route('admin.templates.index') }}" class="mayhen_admin_quick_btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M14.31 8l5.74 9.94M9.69 8h11.48M7.38 12l5.74-9.94M9.69 16L3.95 6.06M14.31 16H2.83m13.79-4l-5.74 9.94"></path>
                        </svg>
                        <span>Kho Mẫu Thiệp</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Wedding Readiness Radar Widget (14-Day Horizon) -->
    <div class="mayhen_admin_card" style="margin-top: 24px; margin-bottom: 0; padding: 20px 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: #FAF3E0; color: var(--mh-admin-gold); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    📡
                </div>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                        Radar Sẵn Sàng Ngày Cưới (14 Ngày Tới)
                    </h3>
                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 2px;">
                        Tự động phát hiện thiếu sót thông tin trước ngày cưới &bull; Điểm sẵn sàng trung bình: <strong>{{ $radarKpis['avg_score'] }}%</strong>
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                @if(($radarKpis['high_risk_count'] ?? 0) > 0)
                    <span class="mayhen_admin_badge mayhen_admin_badge--danger">
                        {{ $radarKpis['high_risk_count'] }} đám cưới rủi ro cao
                    </span>
                @else
                    <span class="mayhen_admin_badge mayhen_admin_badge--success">
                        ✓ 100% Sẵn sàng an toàn
                    </span>
                @endif
                <a href="{{ route('admin.intelligence.radar') }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                    Mở Toàn Bộ Radar &rarr;
                </a>
            </div>
        </div>

        @if(isset($highRiskWeddings) && $highRiskWeddings->isNotEmpty())
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($highRiskWeddings as $rw)
                    <div style="padding: 12px 16px; background: #FFF5F5; border-radius: 8px; border-left: 3px solid #C0392B; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <div style="font-weight: 700; font-size: 0.875rem; color: #900;">
                                {{ $rw['couple'] }} (Ngày cưới: {{ $rw['wedding_date'] ? $rw['wedding_date']->format('d/m/Y') : '—' }} — còn {{ $rw['days_remaining'] }} ngày)
                            </div>
                            <div style="font-size: 0.75rem; color: #555; margin-top: 3px;">
                                Thiếu sót: {{ collect($rw['issues'])->pluck('title')->join(', ') }}
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-weight: 700; font-size: 0.85rem; color: #C0392B;">Điểm: {{ $rw['score'] }}%</span>
                            @if($rw['primary_invitation'])
                                <a href="{{ route('admin.invitations.show', $rw['primary_invitation']->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">Xem thiệp</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="padding: 14px 16px; background: #F6FAF7; border-radius: 8px; border-left: 3px solid #27AE60; font-size: 0.85rem; color: #1B5E20; display: flex; align-items: center; gap: 8px;">
                <span>✨</span>
                <span>Tuyệt vời! Không có đám cưới nào gặp rủi ro lớn trong vòng 14 ngày tới. Tất cả các cặp đôi đều đã chuẩn bị đầy đủ thông tin cốt lõi.</span>
            </div>
        @endif
    </div>

    <!-- 2-Column Split: Recent Orders & Top Templates -->
    <div class="mayhen_admin_dashboard_split" style="margin-top: 24px;">
        <!-- Left: Recent Orders -->
        <div class="mayhen_admin_card" style="margin-bottom: 0;">
            <div class="mayhen_admin_card_header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="mayhen_admin_card_title" style="margin: 0;">Đơn Hàng Gần Đây</div>
                <a href="{{ route('admin.orders.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">Xem tất cả &rarr;</a>
            </div>

            <div class="mayhen_admin_table_wrapper">
                <table class="mayhen_admin_table">
                    <thead>
                        <tr>
                            <th>Mã Đơn</th>
                            <th>Khách Hàng</th>
                            <th>Số Tiền</th>
                            <th style="text-align: center;">Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" style="font-family: monospace; font-weight: 600; color: var(--mh-admin-gold); text-decoration: none;">
                                        {{ $order->order_code }}
                                    </a>
                                </td>
                                <td>
                                    <div style="font-weight: 600; font-size: 0.84375rem;">{{ $order->user->name }}</div>
                                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">{{ $order->plan->name }}</div>
                                </td>
                                <td style="font-weight: 700; color: #1B5E20;">
                                    {{ number_format($order->amount, 0, ',', '.') }}đ
                                </td>
                                <td style="text-align: center;">
                                    @if ($order->isPaid())
                                        <span class="mayhen_admin_badge mayhen_admin_badge_success">Đã Thu</span>
                                    @else
                                        <span class="mayhen_admin_badge mayhen_admin_badge_warning">Chờ CK</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--mh-admin-muted); padding: 24px;">
                                    Chưa có đơn hàng nào phát sinh.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Top Templates -->
        <div class="mayhen_admin_card" style="margin-bottom: 0;">
            <div class="mayhen_admin_card_header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="mayhen_admin_card_title" style="margin: 0;">Mẫu Thiệp Được Yêu Thích</div>
                <a href="{{ route('admin.templates.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">Kho mẫu &rarr;</a>
            </div>

            <div class="mayhen_admin_table_wrapper">
                <table class="mayhen_admin_table">
                    <thead>
                        <tr>
                            <th>Mẫu Thiệp</th>
                            <th style="text-align: right;">Số Thiệp Tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($templates as $tpl)
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <img src="{{ $tpl->thumbnail_url }}" alt="{{ $tpl->name }}" style="width: 36px; height: 36px; object-fit: cover; border-radius: 6px; border: 1px solid var(--mh-admin-border);" onerror="this.onerror=null; this.src='{{ asset('assets/client/images/template-placeholder.webp') }}';">
                                        <div>
                                            <div style="font-weight: 600; font-size: 0.84375rem;">{{ $tpl->name }}</div>
                                            <div style="font-size: 0.6875rem; color: var(--mh-admin-muted);">{{ $tpl->category_label }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: var(--mh-admin-charcoal);">
                                    {{ $tpl->invitations_count }} thiệp
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" style="text-align: center; color: var(--mh-admin-muted); padding: 24px;">
                                    Chưa có dữ liệu mẫu thiệp.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Audit Logs Snapshot -->
    <div class="mayhen_admin_card" style="margin-top: 24px;">
        <div class="mayhen_admin_card_header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="mayhen_admin_card_title" style="margin: 0;">Hoạt Động Quản Trị Gần Đây (Audit Trail)</div>
            @if (Auth::user() && Auth::user()->isSuperAdmin())
                <a href="{{ route('admin.settings.audit_logs') }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">Xem tất cả nhật ký &rarr;</a>
            @endif
        </div>

        <div style="padding: 8px 24px;">
            @forelse ($recentAuditLogs as $log)
                <div class="mayhen_admin_audit_item">
                    <div class="mayhen_admin_audit_icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-size: 0.84375rem; color: var(--mh-admin-charcoal);">
                            <strong>{{ $log['admin_name'] ?? 'System' }}</strong>: {{ $log['description'] ?? '—' }}
                        </div>
                        <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 2px;">
                            {{ isset($log['timestamp']) ? \Carbon\Carbon::parse($log['timestamp'])->diffForHumans() : 'Vừa xong' }} &bull; IP: {{ $log['ip'] ?? '127.0.0.1' }}
                        </div>
                    </div>
                </div>
            @empty
                <div style="text-align: center; color: var(--mh-admin-muted); padding: 20px;">
                    Chưa có hoạt động quản trị nào gần đây.
                </div>
            @endforelse
        </div>
    </div>
@endsection
