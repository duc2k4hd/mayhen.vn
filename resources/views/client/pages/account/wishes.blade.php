@extends('client.layouts.master')

@section('page_title', 'Sổ Lưu Bút & Lời Chúc Mừng Cặp Đôi')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/account.css') }}">
@endpush

@section('content')
<div class="mayhen_account_container">
    <!-- Breadcrumbs -->
    <div class="mayhen_account_breadcrumbs">
        <a href="{{ route('home') }}">Trang chủ</a>
        <span class="mayhen_account_breadcrumbs_sep">/</span>
        <a href="{{ route('account.dashboard') }}">Tài khoản</a>
        <span class="mayhen_account_breadcrumbs_sep">/</span>
        <a href="{{ route('account.invitations') }}">Thiệp cưới</a>
        <span class="mayhen_account_breadcrumbs_sep">/</span>
        <span class="mayhen_account_breadcrumbs_current">Sổ Lưu Bút &amp; Lời Chúc</span>
    </div>

    <!-- Session Feedback Alerts -->
    @if(session('success'))
        <div class="mayhen_account_alert mayhen_account_alert_success">
            <div class="mayhen_account_wishes_alert_row">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mayhen_account_alert mayhen_account_alert_danger">
            <div class="mayhen_account_wishes_alert_row">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mayhen_account_alert mayhen_account_alert_danger">
            <div>
                <strong>Có lỗi xảy ra:</strong>
                <ul class="mayhen_account_wishes_error_list">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Header Card (Clean 2-Tier Quiet Luxury Layout) -->
    <div class="mayhen_account_header_card mayhen_account_wishes_hero">
        <div class="mayhen_account_wishes_hero_top">
            <div class="mayhen_account_wishes_hero_info">
                <span class="mayhen_account_greeting">Sổ Lưu Bút Kỷ Niệm</span>
                <h1 class="mayhen_account_username">
                    Lời Chúc Mừng: {{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}
                </h1>
                <div class="mayhen_account_meta_info">
                    <span>Thiệp cưới: <strong>{{ $invitation->title ?? $invitation->slug }}</strong></span>
                    @if($wedding->wedding_date)
                        <span>&bull; Ngày cưới: {{ \Carbon\Carbon::parse($wedding->wedding_date)->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>

            <div class="mayhen_account_wishes_hero_primary">
                <a href="{{ route('invitation.show', $invitation->slug) }}#loi-chuc" target="_blank" class="mayhen_account_btn mayhen_account_btn_primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                    <span>Xem Thiệp</span>
                </a>
                <a href="{{ route('account.invitations') }}" class="mayhen_account_btn mayhen_account_btn_outline">
                    <span>Về Danh Sách</span>
                </a>
            </div>
        </div>

        <div class="mayhen_account_wishes_hero_tools">
            <div class="mayhen_account_wishes_tool_group">
                <span class="mayhen_account_wishes_tool_label">Lưu giữ kỷ niệm</span>
                <a href="{{ route('memory.vault', $invitation->slug) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_gold mayhen_account_btn_sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"></path>
                    </svg>
                    <span>Bảo Tàng Kỷ Niệm Số</span>
                </a>
                <a href="{{ route('memory.keepsake_book', $invitation->id) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                    <span>Sách Kỷ Yếu (PDF)</span>
                </a>
                <a href="{{ route('memory.settings', $invitation->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.604.852.997 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    <span>Cài Đặt Kỷ Niệm</span>
                </a>
            </div>

            <div class="mayhen_account_wishes_tool_group">
                <a href="{{ route('account.wishes.print', array_merge(['id' => $invitation->id], request()->only(['status']))) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    <span>In Sổ Lưu Bút</span>
                </a>
                <a href="{{ route('account.wishes.export', array_merge(['id' => $invitation->id], request()->only(['q', 'status']))) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>Xuất CSV</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Moderation Mode Control Card -->
    <div class="mayhen_account_wishes_mod_bar">
        <div class="mayhen_account_wishes_mod_info">
            <div class="mayhen_account_wishes_mod_icon" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    <path d="m9 12 2 2 4-4"></path>
                </svg>
            </div>
            <div>
                <div class="mayhen_account_wishes_mod_title">
                    <span>Chế Độ Hiển Thị Lời Chúc:</span>
                    @if($moderationMode === 'manual')
                        <span class="mayhen_account_status_badge locked">Kiểm Duyệt Trước</span>
                    @else
                        <span class="mayhen_account_status_badge published">Tự Động Hiển Thị</span>
                    @endif
                </div>
                <div class="mayhen_account_wishes_mod_desc">
                    @if($moderationMode === 'manual')
                        Lời chúc mới của khách sẽ ở trạng thái chờ duyệt. Chỉ hiển thị trên thiệp sau khi bạn bấm <strong>Hiển thị</strong>.
                    @else
                        Mọi lời chúc công khai từ khách sẽ tự động hiển thị ngay trên thiệp cưới. Bạn có thể tạm ẩn bất kỳ lúc nào.
                    @endif
                </div>
            </div>
        </div>

        <form action="{{ route('account.wishes.update_moderation_mode', $invitation->id) }}" method="POST" class="mayhen_account_wishes_mod_form">
            @csrf
            @if($moderationMode === 'manual')
                <input type="hidden" name="moderation_mode" value="auto">
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm">
                    <span>Chuyển Sang Tự Động Hiển Thị</span>
                </button>
            @else
                <input type="hidden" name="moderation_mode" value="manual">
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                    <span>Bật Kiểm Duyệt Trước</span>
                </button>
            @endif
        </form>
    </div>

    <!-- 6 Interactive Stats Cards (Click to Filter) -->
    <div class="mayhen_account_wishes_kpi_grid">
        <a href="{{ route('account.wishes.index', $invitation->id) }}" class="mayhen_account_wishes_kpi_card {{ empty($filterStatus) ? 'is-active' : '' }}">
            <div class="mayhen_account_wishes_kpi_top">
                <span class="mayhen_account_wishes_kpi_label">Tổng Lời Chúc</span>
                <svg class="mayhen_account_wishes_kpi_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </div>
            <div class="mayhen_account_wishes_kpi_val">{{ number_format($totalWishes) }}</div>
            <div class="mayhen_account_wishes_kpi_sub">Tất cả lời chúc gửi tới cặp đôi</div>
        </a>

        <a href="{{ route('account.wishes.index', ['id' => $invitation->id, 'status' => 'replied']) }}" class="mayhen_account_wishes_kpi_card {{ $filterStatus === 'replied' ? 'is-active' : '' }}">
            <div class="mayhen_account_wishes_kpi_top">
                <span class="mayhen_account_wishes_kpi_label">Đã Cảm Ơn</span>
                <svg class="mayhen_account_wishes_kpi_icon mayhen_account_wishes_kpi_icon--green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="mayhen_account_wishes_kpi_val mayhen_account_wishes_kpi_val--green">{{ number_format($repliedCount) }}</div>
            <div class="mayhen_account_wishes_kpi_sub">Cặp đôi đã phản hồi lại</div>
        </a>

        <a href="{{ route('account.wishes.index', ['id' => $invitation->id, 'status' => 'pending']) }}" class="mayhen_account_wishes_kpi_card {{ $filterStatus === 'pending' ? 'is-active' : '' }}">
            <div class="mayhen_account_wishes_kpi_top">
                <span class="mayhen_account_wishes_kpi_label">Chờ Cảm Ơn</span>
                <svg class="mayhen_account_wishes_kpi_icon mayhen_account_wishes_kpi_icon--amber" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="mayhen_account_wishes_kpi_val mayhen_account_wishes_kpi_val--amber">{{ number_format($pendingReplyCount) }}</div>
            <div class="mayhen_account_wishes_kpi_sub">Khách đang chờ lời cảm ơn</div>
        </a>

        <a href="{{ route('account.wishes.index', ['id' => $invitation->id, 'status' => 'hidden']) }}" class="mayhen_account_wishes_kpi_card {{ $filterStatus === 'hidden' ? 'is-active' : '' }}">
            <div class="mayhen_account_wishes_kpi_top">
                <span class="mayhen_account_wishes_kpi_label">Tạm Ẩn / Chờ Duyệt</span>
                <svg class="mayhen_account_wishes_kpi_icon mayhen_account_wishes_kpi_icon--muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
            </div>
            <div class="mayhen_account_wishes_kpi_val">{{ number_format($hiddenCount) }}</div>
            <div class="mayhen_account_wishes_kpi_sub">Chưa hiển thị trên thiệp</div>
        </a>

        <a href="{{ route('account.wishes.index', ['id' => $invitation->id, 'status' => 'pinned']) }}" class="mayhen_account_wishes_kpi_card {{ $filterStatus === 'pinned' ? 'is-active' : '' }}">
            <div class="mayhen_account_wishes_kpi_top">
                <span class="mayhen_account_wishes_kpi_label">Đã Ghim Nổi Bật</span>
                <svg class="mayhen_account_wishes_kpi_icon mayhen_account_wishes_kpi_icon--gold" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="12" y1="17" x2="12" y2="22"></line>
                    <path d="M5 17h14v-1.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V6h1a2 2 0 0 0 0-4H8a2 2 0 0 0 0 4h1v4.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24Z"></path>
                </svg>
            </div>
            <div class="mayhen_account_wishes_kpi_val mayhen_account_wishes_kpi_val--gold">{{ number_format($pinnedCount) }}</div>
            <div class="mayhen_account_wishes_kpi_sub">Ưu tiên hiển thị trên đầu</div>
        </a>

        <a href="{{ route('account.wishes.index', ['id' => $invitation->id, 'status' => 'private']) }}" class="mayhen_account_wishes_kpi_card {{ $filterStatus === 'private' ? 'is-active' : '' }}">
            <div class="mayhen_account_wishes_kpi_top">
                <span class="mayhen_account_wishes_kpi_label">Lời Chúc Bí Mật</span>
                <svg class="mayhen_account_wishes_kpi_icon mayhen_account_wishes_kpi_icon--purple" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <div class="mayhen_account_wishes_kpi_val mayhen_account_wishes_kpi_val--purple">{{ number_format($privateCount) }}</div>
            <div class="mayhen_account_wishes_kpi_sub">Chỉ cô dâu chú rể đọc được</div>
        </a>
    </div>

    <!-- Quick Filter Pills & Search Toolbar -->
    <div class="mayhen_account_wishes_filter_bar">
        <div class="mayhen_account_wishes_pills" role="navigation" aria-label="Lọc nhanh lời chúc">
            <a href="{{ route('account.wishes.index', array_filter(['id' => $invitation->id, 'q' => $filterSearch])) }}"
               class="mayhen_account_wishes_pill {{ empty($filterStatus) ? 'is-active' : '' }}">
                Tất cả
            </a>
            <a href="{{ route('account.wishes.index', array_filter(['id' => $invitation->id, 'status' => 'pending', 'q' => $filterSearch])) }}"
               class="mayhen_account_wishes_pill {{ $filterStatus === 'pending' ? 'is-active' : '' }}">
                Chờ cảm ơn
            </a>
            <a href="{{ route('account.wishes.index', array_filter(['id' => $invitation->id, 'status' => 'replied', 'q' => $filterSearch])) }}"
               class="mayhen_account_wishes_pill {{ $filterStatus === 'replied' ? 'is-active' : '' }}">
                Đã cảm ơn
            </a>
            <a href="{{ route('account.wishes.index', array_filter(['id' => $invitation->id, 'status' => 'pinned', 'q' => $filterSearch])) }}"
               class="mayhen_account_wishes_pill {{ $filterStatus === 'pinned' ? 'is-active' : '' }}">
                Đã ghim
            </a>
            <a href="{{ route('account.wishes.index', array_filter(['id' => $invitation->id, 'status' => 'hidden', 'q' => $filterSearch])) }}"
               class="mayhen_account_wishes_pill {{ $filterStatus === 'hidden' ? 'is-active' : '' }}">
                Tạm ẩn
            </a>
            <a href="{{ route('account.wishes.index', array_filter(['id' => $invitation->id, 'status' => 'private', 'q' => $filterSearch])) }}"
               class="mayhen_account_wishes_pill {{ $filterStatus === 'private' ? 'is-active' : '' }}">
                Bí mật
            </a>
        </div>

        <div class="mayhen_account_wishes_search_group">
            <form action="{{ route('account.wishes.index', $invitation->id) }}" method="GET" class="mayhen_account_wishes_search_form">
                @if($filterStatus)
                    <input type="hidden" name="status" value="{{ $filterStatus }}">
                @endif
                <div class="mayhen_account_wishes_search_wrap">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="mayhen_account_wishes_search_icon">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="q" value="{{ $filterSearch }}" placeholder="Tìm theo tên hoặc lời chúc..." class="mayhen_account_input mayhen_account_wishes_search_input">
                </div>
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">Tìm</button>
                @if($filterSearch || $filterStatus)
                    <a href="{{ route('account.wishes.index', $invitation->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">Đặt lại</a>
                @endif
            </form>

            @if($hiddenCount > 0)
                <form action="{{ route('account.wishes.approve_all', $invitation->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn cho phép hiển thị toàn bộ {{ $hiddenCount }} lời chúc đang tạm ẩn?');" class="mayhen_account_wish_inline_form">
                    @csrf
                    <button type="submit" class="mayhen_account_btn mayhen_account_btn_sm mayhen_account_wishes_approve_btn">
                        Hiển Thị Tất Cả ({{ $hiddenCount }})
                    </button>
                </form>
            @endif

            <span class="mayhen_account_wishes_count">
                Hiển thị: <strong>{{ $wishes->total() }}</strong> lời chúc
            </span>
        </div>
    </div>

    <!-- Wishes Postcard Grid -->
    @if($wishes->isNotEmpty())
        <div class="mayhen_account_wishes_grid">
            @foreach($wishes as $wish)
                <article class="mayhen_account_wish_card {{ $wish->is_pinned ? 'is-pinned' : '' }}">
                    <div>
                        <div class="mayhen_account_wish_card_head">
                            <div>
                                <div class="mayhen_account_wish_author_row">
                                    <h3 class="mayhen_account_wish_author">{{ $wish->name }}</h3>
                                    @if($wish->is_pinned)
                                        <span class="mayhen_account_status_badge mayhen_account_wish_badge_pin">Đã Ghim</span>
                                    @endif
                                    @if($wish->is_private)
                                        <span class="mayhen_account_status_badge mayhen_account_wish_badge_private">Bí Mật</span>
                                    @endif
                                </div>
                                <div class="mayhen_account_wish_meta">
                                    <span>{{ $wish->created_at ? $wish->created_at->format('d/m/Y H:i') : '' }}</span>
                                    @if($wish->guest)
                                        <span>&bull; Khách mời: <strong>{{ $wish->guest->name }}</strong>@if($wish->guest->group_name) ({{ $wish->guest->group_name }})@endif</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                @if(!$wish->is_approved)
                                    <span class="mayhen_account_status_badge locked">Tạm Ẩn</span>
                                @elseif($wish->reply)
                                    <span class="mayhen_account_status_badge published">Đã Cảm Ơn</span>
                                @else
                                    <span class="mayhen_account_status_badge draft">Chờ Cảm Ơn</span>
                                @endif
                            </div>
                        </div>

                        @if($wish->signature_url || $wish->photo_url)
                            <div class="mayhen_account_wish_media_row">
                                @if($wish->signature_url)
                                    <div class="mayhen_account_wish_sig_chip">
                                        <span>Chữ ký tay:</span>
                                        <img src="{{ $wish->signature_url }}" alt="Chữ ký {{ $wish->name }}" class="mayhen_account_wish_sig_img" loading="lazy">
                                    </div>
                                @endif

                                @if($wish->photo_url)
                                    <div class="mayhen_account_wish_photo_chip">
                                        <span>Ảnh kỷ niệm:</span>
                                        <a href="{{ $wish->photo_url }}" target="_blank" title="Xem ảnh gốc cỡ lớn">
                                            <img src="{{ $wish->photo_url }}" alt="Ảnh từ {{ $wish->name }}" class="mayhen_account_wish_photo_img" loading="lazy">
                                        </a>
                                        <a href="{{ $wish->photo_url }}" target="_blank" class="mayhen_account_wish_photo_link">Phóng to &rarr;</a>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="mayhen_account_wish_quote">
                            "{{ $wish->message }}"
                        </div>

                        @if($wish->reply)
                            <div class="mayhen_account_wish_reply_box">
                                <div class="mayhen_account_wish_reply_head">
                                    <span>Phản hồi từ Cô Dâu &amp; Chú Rể:</span>
                                    <span class="mayhen_account_wish_reply_time">
                                        {{ $wish->replied_at ? $wish->replied_at->format('d/m/Y H:i') : '' }}
                                    </span>
                                </div>
                                <p class="mayhen_account_wish_reply_text">{{ $wish->reply }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="mayhen_account_wish_card_foot">
                        <form action="{{ route('account.wishes.reply', [$invitation->id, $wish->id]) }}" method="POST" class="mayhen_account_wish_reply_form">
                            @csrf
                            <input type="hidden" name="reply_wish_id" value="{{ $wish->id }}">
                            <input type="text"
                                   name="reply"
                                   class="mayhen_account_input mayhen_account_wish_reply_input"
                                   placeholder="{{ $wish->reply ? 'Cập nhật lời cảm ơn...' : 'Nhập lời cảm ơn gửi tới khách...' }}"
                                   value="{{ (old('reply_wish_id') == $wish->id) ? old('reply') : $wish->reply }}"
                                   required>
                            <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm">
                                {{ $wish->reply ? 'Cập Nhật' : 'Gửi Cảm Ơn' }}
                            </button>
                        </form>

                        <div class="mayhen_account_wish_actions_row">
                            <div class="mayhen_account_wish_actions_left">
                                <form action="{{ route('account.wishes.toggle_pin', [$invitation->id, $wish->id]) }}" method="POST" class="mayhen_account_wish_inline_form">
                                    @csrf
                                    <button type="submit" class="mayhen_account_wish_action_btn {{ $wish->is_pinned ? 'is-pinned' : '' }}">
                                        {{ $wish->is_pinned ? 'Bỏ Ghim' : 'Ghim Lên Đầu' }}
                                    </button>
                                </form>

                                <form action="{{ route('account.wishes.toggle_visibility', [$invitation->id, $wish->id]) }}" method="POST" class="mayhen_account_wish_inline_form">
                                    @csrf
                                    <button type="submit" class="mayhen_account_wish_action_btn">
                                        {{ $wish->is_approved ? 'Tạm Ẩn' : 'Hiển Thị' }}
                                    </button>
                                </form>
                            </div>

                            <form action="{{ route('account.wishes.destroy', [$invitation->id, $wish->id]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lời chúc này?');" class="mayhen_account_wish_inline_form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mayhen_account_wish_action_btn mayhen_account_wish_action_btn--danger">
                                    Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @if($wishes->hasPages())
            <div class="mayhen_account_wishes_pagination">
                {{ $wishes->links() }}
            </div>
        @endif
    @else
        <div class="mayhen_account_section mayhen_account_empty_state">
            <svg class="mayhen_account_empty_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
            <h2 class="mayhen_account_empty_title">Chưa Có Lời Chúc Nào</h2>
            <p class="mayhen_account_empty_desc">
                @if($filterSearch || $filterStatus)
                    Không tìm thấy lời chúc nào khớp với bộ lọc hiện tại.
                @else
                    Khách mời chưa gửi lời chúc mừng trên thiệp cưới này. Hãy gửi link thiệp cho bạn bè và người thân để nhận những lời chúc phúc và chữ ký kỷ niệm!
                @endif
            </p>
            <div class="mayhen_account_wishes_empty_actions">
                @if($filterSearch || $filterStatus)
                    <a href="{{ route('account.wishes.index', $invitation->id) }}" class="mayhen_account_btn mayhen_account_btn_primary">
                        Xem Toàn Bộ Lời Chúc
                    </a>
                @else
                    <a href="{{ route('guests.index', $invitation->id) }}" class="mayhen_account_btn mayhen_account_btn_primary">
                        Gửi Thiệp Cho Khách Mời
                    </a>
                    <a href="{{ route('invitation.show', $invitation->slug) }}#loi-chuc" target="_blank" class="mayhen_account_btn mayhen_account_btn_outline">
                        Xem Thiệp Cưới
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
