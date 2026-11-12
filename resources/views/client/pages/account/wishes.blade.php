@extends('client.layouts.master')

@section('page_title', 'Sổ Lưu Bút & Lời Chúc Mừng Cặp Đôi | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/account.css') }}">
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
        <span class="mayhen_account_breadcrumbs_current">Sổ Lưu Bút & Lời Chúc</span>
    </div>

    <!-- Session Feedback Alerts -->
    @if(session('success'))
        <div class="mayhen_account_alert mayhen_account_alert_success">
            <div style="display: flex; align-items: center; gap: 10px;">
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
            <div style="display: flex; align-items: center; gap: 10px;">
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
                <ul style="margin: 6px 0 0 18px; padding: 0;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Header Card -->
    <div class="mayhen_account_header_card">
        <div>
            <span class="mayhen_account_greeting">Sổ Lưu Bút Kỷ Niệm</span>
            <h1 class="mayhen_account_username">
                Lời Chúc Mừng: {{ $wedding->groom_name }} & {{ $wedding->bride_name }}
            </h1>
            <div class="mayhen_account_meta_info">
                <span>Thiệp cưới: <strong>{{ $invitation->title ?? $invitation->slug }}</strong></span>
                @if($wedding->wedding_date)
                    <span>&bull; Ngày cưới: {{ \Carbon\Carbon::parse($wedding->wedding_date)->format('d/m/Y') }}</span>
                @endif
            </div>
        </div>
        <div class="mayhen_account_header_actions">
            <a href="{{ route('memory.vault', $invitation->slug) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_gold">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"></path>
                </svg>
                <span>Bảo Tàng Kỷ Niệm Số</span>
            </a>
            <a href="{{ route('memory.keepsake_book', $invitation->id) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <span>Cuốn Sách Kỷ Yếu (PDF)</span>
            </a>
            <a href="{{ route('memory.settings', $invitation->id) }}" class="mayhen_account_btn mayhen_account_btn_outline">
                <span>⚙️ Cài Đặt Kỷ Niệm</span>
            </a>
            <a href="{{ route('account.wishes.print', array_merge(['id' => $invitation->id], request()->only(['status']))) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                <span>In Sổ Lưu Bút</span>
            </a>
            <a href="{{ route('account.wishes.export', array_merge(['id' => $invitation->id], request()->only(['q', 'status']))) }}" class="mayhen_account_btn mayhen_account_btn_outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Xuất CSV</span>
            </a>
            <a href="{{ route('invitation.show', $invitation->slug) }}#loi-chuc" target="_blank" class="mayhen_account_btn mayhen_account_btn_outline">
                <span>Xem Thiệp</span>
            </a>
            <a href="{{ route('account.invitations') }}" class="mayhen_account_btn mayhen_account_btn_outline">
                <span>Về Danh Sách</span>
            </a>
        </div>
    </div>

    <!-- Moderation Mode Control Card -->
    <div class="mayhen_account_form_card" style="margin-bottom: 24px; padding: 18px 22px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; background: #FAF8F5; border: 1px solid #EFE8DD;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #EFE8DD; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                🛡️
            </div>
            <div>
                <div style="font-size: 15px; font-weight: 600; color: #1F1D1A; display: flex; align-items: center; gap: 8px;">
                    <span>Chế Độ Xuất Bản Lời Chúc:</span>
                    @if($moderationMode === 'manual')
                        <span class="mayhen_account_status_badge locked" style="font-size: 11px;">Kiểm Duyệt Trước</span>
                    @else
                        <span class="mayhen_account_status_badge published" style="font-size: 11px;">Tự Động Hiển Thị</span>
                    @endif
                </div>
                <div style="font-size: 13px; color: #7A726A; margin-top: 2px;">
                    @if($moderationMode === 'manual')
                        Lời chúc mới của khách sẽ ở trạng thái chờ duyệt. Chỉ hiển thị trên thiệp sau khi bạn bấm <strong>Hiển thị</strong>.
                    @else
                        Mọi lời chúc công khai từ khách sẽ tự động hiển thị ngay trên thiệp cưới. Bạn có thể tạm ẩn bất kỳ lúc nào.
                    @endif
                </div>
            </div>
        </div>

        <form action="{{ route('account.wishes.update_moderation_mode', $invitation->id) }}" method="POST" style="margin: 0;">
            @csrf
            @if($moderationMode === 'manual')
                <input type="hidden" name="moderation_mode" value="auto">
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm" style="white-space: nowrap;">
                    ⚡ Chuyển Sang Tự Động Hiển Thị
                </button>
            @else
                <input type="hidden" name="moderation_mode" value="manual">
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" style="white-space: nowrap;">
                    🛡️ Bật Kiểm Duyệt Trước
                </button>
            @endif
        </form>
    </div>

    <!-- 6 Stats Cards Grid -->
    <div class="mayhen_account_kpi_grid" style="margin-bottom: 24px; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="mayhen_account_kpi_card">
            <div class="mayhen_account_kpi_header">
                <span class="mayhen_account_kpi_title">Tổng Lời Chúc</span>
                <svg class="mayhen_account_kpi_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </div>
            <div class="mayhen_account_kpi_val">{{ number_format($totalWishes) }}</div>
            <div class="mayhen_account_kpi_desc">Lời chúc gửi tới đôi uyên ương</div>
        </div>

        <div class="mayhen_account_kpi_card">
            <div class="mayhen_account_kpi_header">
                <span class="mayhen_account_kpi_title">Đã Cảm Ơn</span>
                <svg class="mayhen_account_kpi_icon" style="color: #2E7D32;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="mayhen_account_kpi_val" style="color: #2E7D32;">{{ number_format($repliedCount) }}</div>
            <div class="mayhen_account_kpi_desc">Cặp đôi đã phản hồi lại</div>
        </div>

        <div class="mayhen_account_kpi_card">
            <div class="mayhen_account_kpi_header">
                <span class="mayhen_account_kpi_title">Chờ Cảm Ơn</span>
                <svg class="mayhen_account_kpi_icon" style="color: #F57F17;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="mayhen_account_kpi_val" style="color: #F57F17;">{{ number_format($pendingReplyCount) }}</div>
            <div class="mayhen_account_kpi_desc">Khách mời đang mong lời cảm ơn</div>
        </div>

        <div class="mayhen_account_kpi_card">
            <div class="mayhen_account_kpi_header">
                <span class="mayhen_account_kpi_title">Tạm Ẩn / Chờ Duyệt</span>
                <svg class="mayhen_account_kpi_icon" style="color: #8C827A;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
            </div>
            <div class="mayhen_account_kpi_val">{{ number_format($hiddenCount) }}</div>
            <div class="mayhen_account_kpi_desc">Chưa hiển thị trên thiệp</div>
        </div>

        <div class="mayhen_account_kpi_card">
            <div class="mayhen_account_kpi_header">
                <span class="mayhen_account_kpi_title">Đã Ghim Nổi Bật</span>
                <span style="font-size: 16px;">📌</span>
            </div>
            <div class="mayhen_account_kpi_val" style="color: #B78103;">{{ number_format($pinnedCount) }}</div>
            <div class="mayhen_account_kpi_desc">Ưu tiên hiển thị trên đầu</div>
        </div>

        <div class="mayhen_account_kpi_card">
            <div class="mayhen_account_kpi_header">
                <span class="mayhen_account_kpi_title">Lời Chúc Bí Mật</span>
                <span style="font-size: 16px;">🔒</span>
            </div>
            <div class="mayhen_account_kpi_val" style="color: #6A1B9A;">{{ number_format($privateCount) }}</div>
            <div class="mayhen_account_kpi_desc">Chỉ cô dâu chú rể đọc được</div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="mayhen_account_wishes_toolbar">
        <form action="{{ route('account.wishes.index', $invitation->id) }}" method="GET" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; flex-grow: 1;">
            <div style="position: relative; display: flex; align-items: center; min-width: 240px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="position: absolute; left: 12px; color: #8C827A;">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="q" value="{{ $filterSearch }}" placeholder="Tìm theo tên hoặc lời nhắn..." class="mayhen_account_input" style="height: 38px; padding-left: 36px;">
            </div>

            <select name="status" class="mayhen_account_input" style="height: 38px; width: auto;" onchange="this.form.submit()">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="pinned" {{ $filterStatus === 'pinned' ? 'selected' : '' }}>📌 Đã ghim nổi bật</option>
                <option value="private" {{ $filterStatus === 'private' ? 'selected' : '' }}>🔒 Lời chúc bí mật (Riêng tư)</option>
                <option value="pending" {{ $filterStatus === 'pending' ? 'selected' : '' }}>Chờ phản hồi cảm ơn</option>
                <option value="replied" {{ $filterStatus === 'replied' ? 'selected' : '' }}>Đã gửi lời cảm ơn</option>
                <option value="approved" {{ $filterStatus === 'approved' ? 'selected' : '' }}>Đang hiển thị công khai</option>
                <option value="hidden" {{ $filterStatus === 'hidden' ? 'selected' : '' }}>Tạm ẩn khỏi thiệp</option>
            </select>

            @if($filterSearch || $filterStatus)
                <a href="{{ route('account.wishes.index', $invitation->id) }}" style="font-size: 13px; color: #C62828; text-decoration: none; padding: 6px 10px;">
                    Đặt lại
                </a>
            @endif
        </form>

        <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
            @if($hiddenCount > 0)
                <form action="{{ route('account.wishes.approve_all', $invitation->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn cho phép hiển thị toàn bộ {{ $hiddenCount }} lời chúc đang tạm ẩn?');">
                    @csrf
                    <button type="submit" class="mayhen_account_btn mayhen_account_btn_sm" style="background: #2E7D32; color: #FFFFFF; border: none;">
                        ✓ Hiển Thị Tất Cả ({{ $hiddenCount }})
                    </button>
                </form>
            @endif
            <span style="font-size: 13px; color: #7A726A; white-space: nowrap;">
                Hiển thị: <strong>{{ $wishes->total() }}</strong> lời chúc
            </span>
        </div>
    </div>

    <!-- Wishes Postcard Grid -->
    @if($wishes->isNotEmpty())
        <div class="mayhen_account_wishes_grid">
            @foreach($wishes as $wish)
                <div class="mayhen_account_form_card" style="display: flex; flex-direction: column; justify-content: space-between; gap: 16px; position: relative; {{ $wish->is_pinned ? 'border: 1px solid #E5C378; background: #FFFDF9;' : '' }}">
                    <div>
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 12px;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <h3 style="font-size: 16px; font-weight: 600; color: #1F1D1A; margin: 0;">
                                        {{ $wish->name }}
                                    </h3>
                                    @if($wish->is_pinned)
                                        <span class="mayhen_account_status_badge" style="background: #FFF8E1; color: #B78103; border: 1px solid #FFE082; font-size: 11px;">
                                            📌 Đã Ghim
                                        </span>
                                    @endif
                                    @if($wish->is_private)
                                        <span class="mayhen_account_status_badge" style="background: #F3E5F5; color: #6A1B9A; border: 1px solid #E1BEE7; font-size: 11px;">
                                            🔒 Bí Mật
                                        </span>
                                    @endif
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #8C827A; margin-top: 2px;">
                                    <span>{{ $wish->created_at ? $wish->created_at->format('d/m/Y H:i') : '' }}</span>
                                    @if($wish->guest)
                                        <span>&bull; Khách mời: <strong style="color: #4A453E;">{{ $wish->guest->name }}</strong> @if($wish->guest->group_name)({{ $wish->guest->group_name }})@endif</span>
                                    @endif
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 6px;">
                                @if(!$wish->is_approved)
                                    <span class="mayhen_account_status_badge locked" style="font-size: 11px;">Tạm Ẩn</span>
                                @elseif($wish->reply)
                                    <span class="mayhen_account_status_badge published" style="font-size: 11px;">Đã Cảm Ơn</span>
                                @else
                                    <span class="mayhen_account_status_badge draft" style="font-size: 11px;">Chờ Cảm Ơn</span>
                                @endif
                            </div>
                        </div>

                        <!-- Chữ ký số & Ảnh kỷ niệm quan khách (IDEA-D04 & IDEA-D09) -->
                        @if($wish->signature_url || $wish->photo_url)
                            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 12px;">
                                @if($wish->signature_url)
                                    <div style="padding: 6px 12px; background: #FFFFFF; border: 1px dashed #D8CEBD; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px;">
                                        <span style="font-size: 11px; color: #7A726A;">✍️ Chữ ký tay:</span>
                                        <img src="{{ $wish->signature_url }}" alt="Chữ ký {{ $wish->name }}" style="height: 32px; max-width: 140px; object-fit: contain;">
                                    </div>
                                @endif

                                @if($wish->photo_url)
                                    <div style="padding: 6px 12px; background: #FFFDF9; border: 1px solid #EFE8DD; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px;">
                                        <span style="font-size: 11px; color: #9C7A4A; font-weight: 600;">📷 Ảnh kỷ niệm:</span>
                                        <a href="{{ $wish->photo_url }}" target="_blank" title="Xem ảnh gốc cỡ lớn" style="display: inline-block;">
                                            <img src="{{ $wish->photo_url }}" alt="Ảnh từ {{ $wish->name }}" style="height: 44px; width: 44px; object-fit: cover; border-radius: 4px; border: 1px solid #D8CEBD;" loading="lazy">
                                        </a>
                                        <a href="{{ $wish->photo_url }}" target="_blank" style="font-size: 11px; color: #C5A880; text-decoration: none; font-weight: 500;">Phóng to &rarr;</a>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Nội dung lời chúc -->
                        <div style="background: #FAF8F5; border-left: 3px solid #C5A880; border-radius: 4px 8px 8px 4px; padding: 12px 14px; font-size: 14px; color: #2C2824; font-style: italic; line-height: 1.5; margin-bottom: 16px;">
                            "{{ $wish->message }}"
                        </div>

                        <!-- Câu trả lời của cô dâu chú rể (nếu có) -->
                        @if($wish->reply)
                            <div style="background: #EEF8F1; border: 1px solid #C4E7D0; border-radius: 10px; padding: 12px 14px; margin-bottom: 16px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                    <span style="font-size: 12px; font-weight: 600; color: #1B5E32;">Phản hồi từ Cô Dâu & Chú Rể:</span>
                                    <span style="font-size: 11px; color: #2E7D32;">
                                        {{ $wish->replied_at ? $wish->replied_at->format('d/m/Y H:i') : '' }}
                                    </span>
                                </div>
                                <p style="font-size: 13px; color: #1F1D1A; margin: 0; line-height: 1.4;">
                                    {{ $wish->reply }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Form gửi phản hồi & Nút thao tác -->
                    <div style="border-top: 1px solid #F0EBE3; padding-top: 14px;">
                        <form action="{{ route('account.wishes.reply', [$invitation->id, $wish->id]) }}" method="POST" style="margin-bottom: 12px;">
                            @csrf
                            <input type="hidden" name="reply_wish_id" value="{{ $wish->id }}">
                            <div style="display: flex; gap: 8px;">
                                <input type="text" name="reply" class="mayhen_account_input" style="height: 38px; font-size: 13px;" placeholder="{{ $wish->reply ? 'Cập nhật lời cảm ơn...' : 'Nhập lời cảm ơn gửi tới khách...' }}" value="{{ (old('reply_wish_id') == $wish->id) ? old('reply') : $wish->reply }}" required>
                                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm" style="white-space: nowrap;">
                                    {{ $wish->reply ? 'Cập Nhật' : 'Gửi Cảm Ơn' }}
                                </button>
                            </div>
                        </form>

                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <form action="{{ route('account.wishes.toggle_pin', [$invitation->id, $wish->id]) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" title="{{ $wish->is_pinned ? 'Bỏ ghim' : 'Ghim lời chúc lên đầu' }}" style="background: {{ $wish->is_pinned ? '#FFF8E1' : 'none' }}; border: 1px solid {{ $wish->is_pinned ? '#FFE082' : '#EAE3D9' }}; border-radius: 6px; padding: 6px 10px; font-size: 12px; color: {{ $wish->is_pinned ? '#B78103' : '#5A534B' }}; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;">
                                        📌 {{ $wish->is_pinned ? 'Bỏ Ghim' : 'Ghim' }}
                                    </button>
                                </form>

                                <form action="{{ route('account.wishes.toggle_visibility', [$invitation->id, $wish->id]) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" style="background: none; border: 1px solid #EAE3D9; border-radius: 6px; padding: 6px 10px; font-size: 12px; color: #5A534B; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;">
                                        {{ $wish->is_approved ? '👁️ Tạm ẩn' : '✓ Hiển thị' }}
                                    </button>
                                </form>
                            </div>

                            <form action="{{ route('account.wishes.destroy', [$invitation->id, $wish->id]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lời chúc này?');" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: none; border: 1px solid #FFCDD2; border-radius: 6px; padding: 6px 10px; font-size: 12px; color: #C62828; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;">
                                    🗑️ Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($wishes->hasPages())
            <div style="margin-top: 24px;">
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
                    Không tìm thấy lời chúc nào khớp với bộ lọc tìm kiếm hiện tại.
                @else
                    Khách mời chưa gửi lời chúc mừng trên thiệp cưới này. Hãy gửi link thiệp mời cho bạn bè và người thân để nhận những thông điệp chúc phúc ấm áp!
                @endif
            </p>
            @if($filterSearch || $filterStatus)
                <a href="{{ route('account.wishes.index', $invitation->id) }}" class="mayhen_account_btn mayhen_account_btn_outline" style="margin-top: 8px;">
                    Xem Toàn Bộ Lời Chúc
                </a>
            @endif
        </div>
    @endif
</div>
@endsection

