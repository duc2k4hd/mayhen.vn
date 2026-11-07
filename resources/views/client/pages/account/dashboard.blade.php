@extends('client.pages.account.layout')

@php
    $pageTitle = 'Tổng Quan Tài Khoản';
@endphp

@section('account_breadcrumb', 'Tổng Quan')

@section('account_content')
    <!-- 4 KPI Summary Cards -->
    <div class="mayhen_account_kpi_grid">
        <div class="mayhen_account_kpi_card">
            <div class="mayhen_account_kpi_header">
                <span class="mayhen_account_kpi_title">Thiệp Cưới Đã Tạo</span>
                <svg class="mayhen_account_kpi_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
            </div>
            <div class="mayhen_account_kpi_val">{{ number_format($totalInvitations) }}</div>
            <div class="mayhen_account_kpi_desc">Mẫu thiệp đang quản lý</div>
        </div>

        <div class="mayhen_account_kpi_card">
            <div class="mayhen_account_kpi_header">
                <span class="mayhen_account_kpi_title">Khách Mời Trong Danh Sách</span>
                <svg class="mayhen_account_kpi_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="mayhen_account_kpi_val">{{ number_format($totalGuests) }}</div>
            <div class="mayhen_account_kpi_desc">Tổng số thiệp mời gửi đi</div>
        </div>

        <div class="mayhen_account_kpi_card">
            <div class="mayhen_account_kpi_header">
                <span class="mayhen_account_kpi_title">Xác Nhận Tham Gia (RSVP)</span>
                <svg class="mayhen_account_kpi_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="mayhen_account_kpi_val">{{ number_format($attendingGuests) }}</div>
            <div class="mayhen_account_kpi_desc">Khách báo sẽ đến dự tiệc</div>
        </div>

        <div class="mayhen_account_kpi_card">
            <div class="mayhen_account_kpi_header">
                <span class="mayhen_account_kpi_title">Lời Chúc Phúc</span>
                <svg class="mayhen_account_kpi_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </div>
            <div class="mayhen_account_kpi_val">{{ number_format($totalWishes) }}</div>
            <div class="mayhen_account_kpi_desc">Tình cảm từ bạn bè & gia đình</div>
        </div>
    </div>

    <!-- Active Wedding Highlight Card -->
    @if($activeInvitation)
        @php
            $wedding = $activeInvitation->wedding;
            $groomName = $wedding->groom_name ?? 'Chú Rể';
            $brideName = $wedding->bride_name ?? 'Cô Dâu';
            $templateName = $activeInvitation->template->name ?? 'Mẫu Thiệp Mây Hẹn';
            $coverPhoto = $wedding->cover_photo_url ?? ($activeInvitation->template->thumbnail_url ?? asset('assets/client/images/hero-default.webp'));
            $isLocked = !empty($activeInvitation->custom_config['is_locked']);
            
            // Tính số ngày đếm ngược
            $daysLeft = null;
            if ($wedding && $wedding->wedding_date) {
                $weddingCarbon = \Carbon\Carbon::parse($wedding->wedding_date)->startOfDay();
                $today = now()->startOfDay();
                $diff = (int) $today->diffInDays($weddingCarbon, false);
                $daysLeft = $diff;
            }
        @endphp
        <div class="mayhen_account_hero_wedding">
            <div class="mayhen_account_hero_photo_wrap">
                <img src="{{ $coverPhoto }}" alt="{{ $groomName }} & {{ $brideName }}" class="mayhen_account_hero_photo" onerror="this.src='{{ asset('assets/client/images/hero-default.webp') }}'">
                <div class="mayhen_account_hero_photo_overlay">
                    <span class="mayhen_account_hero_template_badge">{{ $templateName }}</span>
                    <h2 class="mayhen_account_hero_couple_name">{{ $groomName }} & {{ $brideName }}</h2>
                </div>
            </div>

            <div class="mayhen_account_hero_content">
                <div>
                    <div class="mayhen_account_hero_top_info">
                        <div class="mayhen_account_hero_date">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <span>
                                @if($wedding && $wedding->wedding_date)
                                    Ngày cưới: <strong>{{ \Carbon\Carbon::parse($wedding->wedding_date)->format('d/m/Y') }}</strong>
                                @else
                                    Chưa thiết lập ngày cưới
                                @endif
                            </span>
                        </div>

                        @if($daysLeft !== null)
                            <div class="mayhen_account_hero_countdown">
                                @if($daysLeft > 0)
                                    Còn {{ $daysLeft }} ngày nữa
                                @elseif($daysLeft === 0)
                                    Hôm nay là Ngày Trọng Đại!
                                @else
                                    Đã qua {{ abs($daysLeft) }} ngày kỷ niệm
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="mayhen_account_hero_status_row">
                        @if($isLocked)
                            <span class="mayhen_account_status_badge locked">
                                <span class="mayhen_account_status_dot"></span>
                                Tạm Khóa Bởi Quản Trị
                            </span>
                        @elseif($activeInvitation->is_published)
                            <span class="mayhen_account_status_badge published">
                                <span class="mayhen_account_status_dot"></span>
                                Đang Xuất Bản Trực Tuyến
                            </span>
                        @else
                            <span class="mayhen_account_status_badge draft">
                                <span class="mayhen_account_status_dot"></span>
                                Chế Độ Bản Nháp (Riêng Tư)
                            </span>
                        @endif

                        @if($activeInvitation->expires_at)
                            <span style="font-size: 12px; color: #7A726A;">
                                Hạn lưu trữ: {{ $activeInvitation->expires_at->format('d/m/Y') }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Link chia sẻ thiệp -->
                <div class="mayhen_account_hero_link_box">
                    <span style="font-weight: 500;">Liên kết thiệp cưới:</span>
                    <a href="{{ route('invitation.show', $activeInvitation->slug) }}" target="_blank" class="mayhen_account_hero_link_url" id="mayhen-invitation-url">
                        {{ url('/thiep/' . $activeInvitation->slug) }}
                    </a>
                    <button type="button" class="mayhen_account_copy_btn" onclick="navigator.clipboard.writeText('{{ url('/thiep/' . $activeInvitation->slug) }}'); this.innerText='Đã chép!'; setTimeout(() => this.innerText='Sao chép', 2000);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        Sao chép
                    </button>
                </div>

                <!-- Action Buttons -->
                <div class="mayhen_account_hero_actions">
                    <a href="{{ route('invitation.create', ['edit' => $activeInvitation->slug]) }}" class="mayhen_account_btn mayhen_account_btn_primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                        </svg>
                        <span>Chỉnh Sửa Thiệp Trong Studio</span>
                    </a>

                    <a href="{{ route('invitation.show', $activeInvitation->slug) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_outline">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <span>Xem Thiệp Trực Tiếp</span>
                    </a>

                    <a href="{{ route('guests.index', $activeInvitation->id) }}" class="mayhen_account_btn mayhen_account_btn_outline">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Quản Lý Khách Mời</span>
                    </a>

                    <a href="{{ route('account.gifts.index', $activeInvitation->id) }}" class="mayhen_account_btn mayhen_account_btn_outline" title="Quản lý tiền mừng cưới">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                            <line x1="2" y1="10" x2="22" y2="10"></line>
                        </svg>
                        <span>Sổ Mừng Cưới</span>
                    </a>

                    <button type="button"
                        class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_qr_trigger"
                        data-invitation-url="{{ url('/thiep/' . $activeInvitation->slug) }}"
                        data-couple-name="{{ $groomName }} & {{ $brideName }}"
                        data-wedding-date="{{ $wedding && $wedding->wedding_date ? \Carbon\Carbon::parse($wedding->wedding_date)->format('d/m/Y') : '' }}"
                        data-template-name="{{ $templateName }}"
                        data-invitation-slug="{{ $activeInvitation->slug }}"
                        title="Tạo mã QR & thẻ in thiệp cưới">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span>Mã QR & Thẻ In</span>
                    </button>

                    <a href="{{ route('account.invitations.settings', $activeInvitation->id) }}" class="mayhen_account_btn mayhen_account_btn_outline" title="Cài đặt thiệp cưới">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        <span>Cài Đặt Thiệp</span>
                    </a>

                    @if(!$isLocked)
                        <form action="{{ route('account.invitations.toggle_publish', $activeInvitation->id) }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="mayhen_account_btn mayhen_account_btn_outline" title="{{ $activeInvitation->is_published ? 'Tạm ẩn thiệp về bản nháp' : 'Công khai thiệp' }}">
                                @if($activeInvitation->is_published)
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    </svg>
                                    <span>Tạm Dừng Xuất Bản</span>
                                @else
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M5 12h14"></path>
                                        <path d="m12 5 7 7-7 7"></path>
                                    </svg>
                                    <span>Xuất Bản Thiệp Ngay</span>
                                @endif
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @else
        <!-- Empty State khi chưa có thiệp cưới nào -->
        <div class="mayhen_account_section mayhen_account_empty_state">
            <svg class="mayhen_account_empty_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <h2 class="mayhen_account_empty_title">Bạn Chưa Tạo Mẫu Thiệp Cưới Nào</h2>
            <p class="mayhen_account_empty_desc">
                Hãy bắt đầu tạo tác phẩm thiệp cưới online mang đậm dấu ấn tình yêu của riêng hai bạn với các mẫu thiết kế Quiet Luxury tinh tế từ Mây Hẹn.
            </p>
            <a href="{{ route('templates.index') }}" class="mayhen_account_btn mayhen_account_btn_gold" style="margin-top: 8px;">
                <span>Khám Phá Kho Mẫu Thiệp & Bắt Đầu</span>
            </a>
        </div>
    @endif

    <!-- Quick Action Shortcuts -->
    <div class="mayhen_account_quick_actions">
        <a href="{{ $activeInvitation ? route('guests.index', $activeInvitation->id) : route('account.invitations') }}" class="mayhen_account_quick_card">
            <div>
                <div class="mayhen_account_quick_header">
                    <div class="mayhen_account_quick_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <h3 class="mayhen_account_quick_title">Khách Mời & RSVP</h3>
                </div>
                <p class="mayhen_account_quick_desc" style="margin-top: 12px;">
                    Tạo thiệp cá nhân hóa kèm tên từng khách mời, theo dõi số lượng người tham dự tiệc cưới thời gian thực.
                </p>
            </div>
            <span class="mayhen_account_quick_link_text">
                Vào quản lý khách mời
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </span>
        </a>

        <a href="{{ route('pricing') }}" class="mayhen_account_quick_card">
            <div>
                <div class="mayhen_account_quick_header">
                    <div class="mayhen_account_quick_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                    </div>
                    <h3 class="mayhen_account_quick_title">Nâng Cấp Gói Thiệp</h3>
                </div>
                <p class="mayhen_account_quick_desc" style="margin-top: 12px;">
                    Mở khóa tính năng Không Giới Hạn Khách Mời, Album 10 Ảnh Full HD, Bản đồ chỉ đường và Hiệu ứng chuyển động lụa.
                </p>
            </div>
            <span class="mayhen_account_quick_link_text">
                Xem bảng giá chi tiết
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </span>
        </a>

        <a href="{{ route('account.orders') }}" class="mayhen_account_quick_card">
            <div>
                <div class="mayhen_account_quick_header">
                    <div class="mayhen_account_quick_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                            <line x1="2" y1="10" x2="22" y2="10"></line>
                        </svg>
                    </div>
                    <h3 class="mayhen_account_quick_title">Đơn Hàng Đã Mua</h3>
                </div>
                <p class="mayhen_account_quick_desc" style="margin-top: 12px;">
                    Kiểm tra trạng thái thanh toán VietQR, hóa đơn gói cước và thời hạn lưu trữ thiệp cưới trên hệ thống.
                </p>
            </div>
            <span class="mayhen_account_quick_link_text">
                Xem lịch sử đơn hàng
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </span>
        </a>

        <!-- Thẻ 4: Sơ Đồ Xếp Bàn Tiệc & In Tiếp Tân (TASK-V3-02) -->
        <a href="{{ $activeInvitation ? route('seating.index', $activeInvitation->id) : route('account.invitations') }}" class="mayhen_account_quick_card">
            <div>
                <div class="mayhen_account_quick_header">
                    <div class="mayhen_account_quick_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <circle cx="12" cy="12" r="4"></circle>
                            <line x1="12" y1="2" x2="12" y2="6"></line>
                            <line x1="12" y1="18" x2="12" y2="22"></line>
                            <line x1="2" y1="12" x2="6" y2="12"></line>
                            <line x1="18" y1="12" x2="22" y2="12"></line>
                        </svg>
                    </div>
                    <h3 class="mayhen_account_quick_title">Sơ Đồ Xếp Bàn Tiệc</h3>
                </div>
                <p class="mayhen_account_quick_desc" style="margin-top: 12px;">
                    Sắp xếp khách vào từng bàn tiệc trực quan, tính toán tải bàn và in sơ đồ đón khách chuẩn A4 cho sảnh tiệc.
                </p>
            </div>
            <span class="mayhen_account_quick_link_text">
                Vào xếp bàn tiệc cưới
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </span>
        </a>

        <!-- Thẻ 5: Màn Chiếu LED & Điều Phối Sự Kiện (TASK-V2-02 & TASK-V3-04) -->
        <a href="{{ $activeInvitation ? route('live_wall.settings', $activeInvitation->id) : route('account.invitations') }}" class="mayhen_account_quick_card">
            <div>
                <div class="mayhen_account_quick_header">
                    <div class="mayhen_account_quick_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <h3 class="mayhen_account_quick_title">Màn Chiếu & Điều Phối</h3>
                </div>
                <p class="mayhen_account_quick_desc" style="margin-top: 12px;">
                    Bức tường trình chiếu ảnh cưới & lời chúc 60fps trên màn hình LED sân khấu và điều phối đón khách thời gian thực.
                </p>
            </div>
            <span class="mayhen_account_quick_link_text">
                Thiết lập màn chiếu LED
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </span>
        </a>

        <!-- Thẻ 6: Két Kỷ Niệm & Hộp Thời Gian (TASK-V2-03 & TASK-V3-05) -->
        <a href="{{ $activeInvitation ? route('memory.settings', $activeInvitation->id) : route('account.invitations') }}" class="mayhen_account_quick_card">
            <div>
                <div class="mayhen_account_quick_header">
                    <div class="mayhen_account_quick_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>
                    </div>
                    <h3 class="mayhen_account_quick_title">Két Kỷ Niệm & Hộp Thư</h3>
                </div>
                <p class="mayhen_account_quick_desc" style="margin-top: 12px;">
                    Lưu giữ trọn đời lời chúc phúc, bộ sưu tập ảnh khách chụp, xuất sách kỷ yếu in ấn và tâm thư niêm phong sáp.
                </p>
            </div>
            <span class="mayhen_account_quick_link_text">
                Mở két kỷ niệm số
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </span>
        </a>
    </div>

    <!-- Recent Wishes from Guests -->
    <div class="mayhen_account_section">
        <div class="mayhen_account_section_header">
            <h2 class="mayhen_account_section_title">Lời Chúc Phúc Mới Nhất Từ Quan Khách</h2>
            @if($activeInvitation)
                <div style="display: flex; align-items: center; gap: 14px;">
                    <a href="{{ route('account.wishes.index', $activeInvitation->id) }}" style="font-size: 13px; color: #1F1D1A; text-decoration: none; font-weight: 600;">
                        Quản Lý Sổ Lưu Bút ({{ $totalWishes }}) &rarr;
                    </a>
                    <a href="{{ route('invitation.show', $activeInvitation->slug) }}#loi-chuc" target="_blank" style="font-size: 13px; color: #9C7A4A; text-decoration: none; font-weight: 500;">
                        Xem trên thiệp &rarr;
                    </a>
                </div>
            @endif
        </div>

        @if($recentWishes->isNotEmpty())
            <div class="mayhen_account_wishes_list">
                @foreach($recentWishes as $wish)
                    <div class="mayhen_account_wish_item">
                        <div class="mayhen_account_wish_meta">
                            <span class="mayhen_account_wish_author">{{ $wish->name ?? 'Khách quý' }}</span>
                            <span>{{ $wish->created_at ? $wish->created_at->format('d/m/Y H:i') : '' }}</span>
                        </div>
                        <div class="mayhen_account_wish_text">
                            "{{ $wish->message }}"
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 32px 0; color: #8C827A; font-size: 14px;">
                Chưa có lời chúc nào được gửi đến. Hãy gửi link thiệp cưới cho bạn bè và người thân nhé!
            </div>
        @endif
    </div>
@endsection
