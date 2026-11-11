@extends('client.pages.account.layout')

@php
    $pageTitle = 'Thiệp Cưới Của Tôi';
@endphp

@section('account_breadcrumb', 'Thiệp Cưới Của Tôi')

@section('account_content')
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 600; color: #1F1D1A; margin: 0;">
                Danh Sách Thiệp Cưới ({{ $invitations->total() }})
            </h2>
            <p style="font-size: 13px; color: #7A726A; margin: 4px 0 0 0;">
                Quản lý các mẫu thiệp cưới đang chuẩn bị cho ngày hôn lễ trọng đại của hai bạn.
            </p>
        </div>
        <a href="{{ route('templates.index') }}" class="mayhen_account_btn mayhen_account_btn_gold mayhen_account_btn_sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Tạo Thêm Mẫu Mới</span>
        </a>
    </div>

    {{-- Hộp Nhập Mã Bàn Giao Từ Studio --}}
    <div style="background: #FAF8F5; border: 1px dashed #C5A880; border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
        <div>
            <strong style="display: block; font-size: 14px; color: #1F1D1A; margin-bottom: 2px;">
                🔑 Bạn có mã nhận thiệp cưới từ Studio / Wedding Planner?
            </strong>
            <span style="font-size: 12px; color: #7A726A;">
                Nhập mã bàn giao 6 ký tự do Studio cung cấp để nhận quyền quản lý thiệp cưới của hai bạn.
            </span>
        </div>
        <form action="{{ route('account.claim_partner_invitation') }}" method="POST" style="display: flex; gap: 8px; margin: 0;">
            @csrf
            <input 
                type="text" 
                name="handover_code" 
                maxlength="6" 
                placeholder="Ví dụ: AB88CD" 
                required 
                style="padding: 6px 12px; border: 1px solid #DCD5CB; border-radius: 6px; font-family: monospace; font-weight: 700; text-transform: uppercase; width: 130px; font-size: 13px;"
            >
            <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm">
                Nhận Thiệp
            </button>
        </form>
    </div>

    @if($invitations->isNotEmpty())
        <div class="mayhen_account_invitations_grid">
            @foreach($invitations as $inv)
                @php
                    $wedding = $inv->wedding;
                    $groomName = $wedding->groom_name ?? 'Chú Rể';
                    $brideName = $wedding->bride_name ?? 'Cô Dâu';
                    $templateName = $inv->template->name ?? 'Mẫu Thiệp Mây Hẹn';
                    $coverPhoto = $wedding->cover_photo_url ?: ($inv->template->thumbnail_url ?: asset('assets/client/images/hero-default.webp'));
                    $isLocked = !empty($inv->custom_config['is_locked']);
                    $guestCount = $wedding ? $wedding->guests->count() : 0;
                @endphp
                <div class="mayhen_account_invitation_card">
                    <div class="mayhen_account_card_media">
                        <img src="{{ $coverPhoto }}" alt="{{ $groomName }} & {{ $brideName }}" class="mayhen_account_card_img" onerror="this.onerror=null; this.src='{{ asset('assets/client/images/hero-default.webp') }}';">
                        <span class="mayhen_account_card_badge">{{ $templateName }}</span>

                        <div style="position: absolute; bottom: 10px; right: 10px;">
                            @if($isLocked)
                                <span class="mayhen_account_status_badge locked" style="background: rgba(255,235,238,0.9); backdrop-filter: blur(4px);">
                                    <span class="mayhen_account_status_dot"></span>
                                    Bị Khóa
                                </span>
                            @elseif($inv->is_published)
                                <span class="mayhen_account_status_badge published" style="background: rgba(232,245,233,0.9); backdrop-filter: blur(4px);">
                                    <span class="mayhen_account_status_dot"></span>
                                    Đang Xuất Bản
                                </span>
                            @else
                                <span class="mayhen_account_status_badge draft" style="background: rgba(255,248,225,0.9); backdrop-filter: blur(4px);">
                                    <span class="mayhen_account_status_dot"></span>
                                    Bản Nháp
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mayhen_account_card_body">
                        <h3 class="mayhen_account_card_title">{{ $groomName }} & {{ $brideName }}</h3>

                        <div class="mayhen_account_card_date">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <span>
                                @if($wedding && $wedding->wedding_date)
                                    {{ \Carbon\Carbon::parse($wedding->wedding_date)->format('d/m/Y') }}
                                @else
                                    Chưa đặt ngày cưới
                                @endif
                            </span>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #8C827A; flex-wrap: wrap; gap: 4px;">
                            <span>Khách mời: <strong>{{ $guestCount }}</strong></span>
                            @if($inv->expires_at)
                                @php
                                    $isExpired = $inv->expires_at->isPast();
                                    $daysDiff = (int) now()->diffInDays($inv->expires_at, false);
                                @endphp
                                @if(!$isExpired && $daysDiff <= 14)
                                    <span style="color: #B45309; font-weight: 600; background: #FEF3C7; padding: 2px 6px; border-radius: 4px;">
                                        ⚠️ Còn {{ $daysDiff }} ngày
                                    </span>
                                @elseif($isExpired && abs($daysDiff) <= 7)
                                    <span style="color: #C62828; font-weight: 600; background: #FFEBEE; padding: 2px 6px; border-radius: 4px;">
                                        ⏳ Ân hạn (còn {{ 7 - abs($daysDiff) }} ngày)
                                    </span>
                                @elseif($isExpired)
                                    <span style="color: #616161; font-weight: 600; background: #EEEEEE; padding: 2px 6px; border-radius: 4px;">
                                        🔒 Hết hạn lưu trữ
                                    </span>
                                @else
                                    <span>Hạn dùng: {{ $inv->expires_at->format('d/m/Y') }}</span>
                                @endif
                            @else
                                <span>Không thời hạn</span>
                            @endif
                        </div>

                        {{-- Bảng Đánh Giá Độ Sẵn Sàng Ngày Cưới (TASK-VAL-12) --}}
                        @php
                            $readiness = $readinessMap[$inv->id] ?? null;
                        @endphp
                        @if($readiness)
                            <div class="mayhen_readiness_indicator">
                                <div class="mayhen_readiness_header">
                                    <span class="mayhen_readiness_title">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                        Sẵn sàng: <strong style="color: {{ $readiness['score'] >= 85 ? '#2E7D32' : ($readiness['score'] >= 50 ? '#C5A880' : '#C62828') }};">{{ $readiness['score'] }}%</strong>
                                    </span>
                                    <button 
                                        type="button" 
                                        class="mayhen_readiness_toggle_btn" 
                                        data-invitation-id="{{ $inv->id }}"
                                        data-couple-name="{{ $groomName }} & {{ $brideName }}"
                                        data-readiness="{{ json_encode($readiness) }}"
                                    >
                                        Xem 7 bước ({{ $readiness['passed_count'] }}/{{ $readiness['total_count'] }})
                                    </button>
                                </div>
                                <div class="mayhen_readiness_track">
                                    <div class="mayhen_readiness_fill" style="background: {{ $readiness['score'] >= 85 ? '#2E7D32' : ($readiness['score'] >= 50 ? '#C5A880' : '#D9B4A7') }}; width: {{ $readiness['score'] }}%;"></div>
                                </div>
                            </div>
                        @endif

                        <!-- Link thiệp công khai -->
                        <div style="background: #FAF8F5; border: 1px dashed #EAE3D9; border-radius: 8px; padding: 8px 10px; display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 12px;">
                            <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-family: monospace; color: #9C7A4A;">
                                {{ url('/thiep/' . $inv->slug) }}
                            </span>
                            <button type="button" class="mayhen_account_copy_btn" style="padding: 3px 8px; font-size: 11px;" onclick="navigator.clipboard.writeText('{{ url('/thiep/' . $inv->slug) }}'); this.innerText='Đã chép!'; setTimeout(() => this.innerText='Chép', 2000);">
                                Chép
                            </button>
                        </div>

                        <!-- 1. Cặp nút hành động chính (Primary Bar) -->
                        <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 8px; margin-top: 14px;">
                            <a href="{{ route('invitation.create', ['edit' => $inv->slug]) }}" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 20h9"></path>
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                </svg>
                                <span>Vào Studio</span>
                            </a>

                            <a href="{{ route('invitation.show', $inv->slug) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <span>Xem Thiệp</span>
                            </a>
                        </div>

                        <!-- 2. Hệ sinh thái công cụ hỗ trợ đám cưới (Feature Grid 2 cột cân xứng) -->
                        <div class="mayhen_account_card_actions" style="margin-top: 10px; padding-top: 10px;">
                            <a href="{{ route('account.planner.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Kế hoạch việc cần làm & dự toán chi phí">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M9 11l3 3L22 4"></path>
                                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                </svg>
                                <span>Kế Hoạch & Chi Phí</span>
                            </a>

                            <a href="{{ route('account.events.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Quản lý đa sự kiện: Lễ Vu Quy, Thành Hôn, Tiệc Cưới">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <span>Sự Kiện & Lễ</span>
                            </a>

                            <a href="{{ route('guests.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Quản lý danh sách khách mời & RSVP">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                </svg>
                                <span>Khách Mời</span>
                            </a>

                            <a href="{{ route('account.invitations.analytics', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Báo cáo phân tích tương tác, lượt xem & phễu chuyển đổi">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <line x1="18" y1="20" x2="18" y2="10"></line>
                                    <line x1="12" y1="20" x2="12" y2="4"></line>
                                    <line x1="6" y1="20" x2="6" y2="14"></line>
                                </svg>
                                <span>Thống Kê</span>
                            </a>

                            <a href="{{ route('seating.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Sơ đồ xếp bàn tiệc cưới">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <circle cx="12" cy="12" r="4"></circle>
                                </svg>
                                <span>Xếp Bàn</span>
                            </a>

                            <a href="{{ route('coordination.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Bảng điều khiển & điều phối ngày cưới realtime">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                </svg>
                                <span>Điều Phối</span>
                            </a>

                            <a href="{{ route('live_wall.settings', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Màn chiếu LED sân khấu thời gian thực">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                    <line x1="8" y1="21" x2="16" y2="21"></line>
                                    <line x1="12" y1="17" x2="12" y2="21"></line>
                                </svg>
                                <span>Màn Chiếu</span>
                            </a>

                            <a href="{{ route('account.wishes.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Sổ lưu bút & lời chúc phúc">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                                <span>Sổ Lưu Bút</span>
                            </a>

                            <a href="{{ route('account.webhooks.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Tự động hóa & Webhook (Telegram, Google Sheets, Zapier)">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                                </svg>
                                <span>Webhook</span>
                            </a>

                            <a href="{{ route('account.gifts.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Quản lý tiền mừng cưới & VietQR">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                    <line x1="2" y1="10" x2="22" y2="10"></line>
                                </svg>
                                <span>Sổ Mừng</span>
                            </a>

                            <a href="{{ route('photo_drop.manage', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Quản lý ảnh do khách mời chụp tại bàn tiệc">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                                <span>Ảnh Khách</span>
                            </a>

                            <a href="{{ route('memory.settings', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Két lưu trữ kỷ niệm số & sách kỷ yếu lưu bút">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                                </svg>
                                <span>Két Kỷ Niệm</span>
                            </a>

                            <a href="{{ route('account.keepsake.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Lưu giữ kỷ niệm hậu sự kiện, tâm thư cảm ơn & chế độ kỷ niệm">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                                <span>Lưu Giữ Kỷ Niệm</span>
                            </a>

                            <a href="{{ route('account.paper_scan.create', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Quét ảnh thiệp cưới giấy & tự động trích xuất thông tin bằng AI">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                                <span>Quét Thiệp Giấy AI</span>
                            </a>

                            <a href="{{ route('account.stationery.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Bộ ấn phẩm & thiệp đồng bộ đa định dạng (Story 9:16, Standee bàn tiệc, Save the Date)">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                <span>Bộ Thiệp Đồng Bộ</span>
                            </a>

                            <a href="{{ route('account.time_capsule.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Hộp thời gian kỷ niệm tương lai (niêm phong sáp)">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                <span>Hộp Thời Gian</span>
                            </a>

                            <a href="{{ route('account.reschedule.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Thông báo dời ngày cưới & yêu cầu quan khách xác nhận lại RSVP">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                <span>Dời Lịch Cưới</span>
                            </a>

                            <a href="{{ route('account.wedding_media.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Quản lý kho tệp hình ảnh & hạn mức dung lượng lưu trữ">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                <span>Kho Tệp & Dung Lượng</span>
                            </a>

                            <button type="button" 
                                class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm mayhen_account_qr_trigger"
                                data-invitation-url="{{ url('/thiep/' . $inv->slug) }}"
                                data-couple-name="{{ $groomName }} & {{ $brideName }}"
                                data-wedding-date="{{ $wedding && $wedding->wedding_date ? \Carbon\Carbon::parse($wedding->wedding_date)->format('d/m/Y') : '' }}"
                                data-template-name="{{ $templateName }}"
                                data-invitation-slug="{{ $inv->slug }}"
                                title="Tạo mã QR & thẻ in thiệp cưới">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="14" width="7" height="7"></rect>
                                    <rect x="3" y="14" width="7" height="7"></rect>
                                </svg>
                                <span>Mã QR</span>
                            </button>

                            <a href="{{ route('account.invitations.domain', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Cấu hình tên miền riêng & subdomain">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="2" y1="12" x2="22" y2="12"></line>
                                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                </svg>
                                <span>Tên Miền</span>
                            </a>

                            <a href="{{ route('account.invitations.settings', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Cài đặt thiệp cưới">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                </svg>
                                <span>Cài Đặt</span>
                            </a>

                            <a href="{{ route('account.invitations.resilience_kit', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Bộ tài liệu in ấn dự phòng sự cố ngày cưới A4/A3">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                    <rect x="6" y="14" width="12" height="8"></rect>
                                </svg>
                                <span>Bộ Dự Phòng</span>
                            </a>

                            <a href="{{ route('account.invitations.vendor_brief', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Tài liệu tác nghiệp tối giản cho MC, Nhà hàng & Ekip ảnh">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                                <span>Bản Đối Tác</span>
                            </a>

                            <a href="{{ route('memory.offline_archive', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Xuất toàn bộ kỷ niệm hôn lễ thành gói ZIP ngoại tuyến độc lập kèm mã SHA-256">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                <span>Gói Ngoại Tuyến</span>
                            </a>
                        </div>

                        <div style="margin-top: 8px;">
                            @if(!$isLocked)
                                <form action="{{ route('account.invitations.toggle_publish', $inv->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" style="width: 100%;">
                                        {{ $inv->is_published ? 'Tạm Dừng Xuất Bản' : 'Xuất Bản Trực Tuyến' }}
                                    </button>
                                </form>
                            @else
                                <button type="button" disabled class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" style="width: 100%; opacity: 0.6; cursor: not-allowed;">
                                    Tạm Khóa Bởi Quản Trị
                                </button>
                            @endif

                            {{-- Nút Tự Phục Vụ Gia Hạn & Nâng Cấp Hạn Dùng (Mục 13.4 DOCX) --}}
                            <button type="button" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm btn-open-renewal-modal" data-invitation-id="{{ $inv->id }}" style="width: 100%; margin-top: 6px; border-color: #C5A880; color: #9C7A4A; font-weight: 600;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <span>Gia Hạn & Nâng Cấp</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($invitations->hasPages())
            <div style="margin-top: 24px;">
                {{ $invitations->links() }}
            </div>
        @endif
    @else
        <div class="mayhen_account_section mayhen_account_empty_state">
            <svg class="mayhen_account_empty_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <h2 class="mayhen_account_empty_title">Chưa Có Thiệp Cưới Nào</h2>
            <p class="mayhen_account_empty_desc">
                Bạn chưa khởi tạo mẫu thiệp cưới nào. Hãy duyệt qua bộ sưu tập mẫu thiệp nghệ thuật của Mây Hẹn và tạo thiệp ngay hôm nay.
            </p>
            <a href="{{ route('templates.index') }}" class="mayhen_account_btn mayhen_account_btn_gold" style="margin-top: 8px;">
                <span>Xem Kho Mẫu Thiệp</span>
            </a>
        </div>
    @endif

    {{-- Modal Tự Phục Vụ Gia Hạn & Nâng Cấp Hạn Dùng Thiệp Cưới (Mục 13.4 DOCX) --}}
    <div id="mayhen_renewal_modal" style="display: none; position: fixed; inset: 0; background: rgba(31, 29, 26, 0.7); backdrop-filter: blur(6px); z-index: 99999; align-items: center; justify-content: center; padding: 16px;">
        <div style="background: #FFFFFF; border-radius: 18px; width: 100%; max-width: 580px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 50px rgba(0,0,0,0.25); display: flex; flex-direction: column;">
            <div style="padding: 18px 24px; border-bottom: 1px solid #EAE3D9; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 20px;">⚡</span>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; font-weight: 700; color: #1F1D1A; margin: 0;">Gia Hạn & Nâng Cấp Thời Lượng</h3>
                </div>
                <button type="button" id="btn-close-renewal-modal" style="background: none; border: none; font-size: 20px; color: #7A726A; cursor: pointer; padding: 4px;">✕</button>
            </div>
            
            <div style="padding: 24px; display: flex; flex-direction: column; gap: 18px;">
                {{-- Thông tin hiện tại --}}
                <div style="background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <span style="font-size: 11px; letter-spacing: 1px; color: #9C7A4A; font-weight: 700; text-transform: uppercase;">Trạng Thái Hạn Dùng</span>
                        <h4 id="renewal-inv-title" style="font-size: 14px; color: #1F1D1A; margin: 2px 0;">Đang tải...</h4>
                        <span id="renewal-inv-expiry" style="font-size: 12px; color: #7A726A;">Hạn hiện tại: ...</span>
                    </div>
                    <span id="renewal-status-badge" style="padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; background: #EFE8DD; color: #6B5E4B;">Đang kiểm tra</span>
                </div>

                <div>
                    <h4 style="font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 10px;">Chọn gói gia hạn / nâng cấp mong muốn:</h4>
                    <div id="renewal-plans-list" style="display: flex; flex-direction: column; gap: 10px;">
                        <div style="text-align: center; padding: 20px; color: #7A726A; font-style: italic;">Đang tải danh sách gói...</div>
                    </div>
                </div>
            </div>

            <div style="padding: 16px 24px; background: #FAF8F5; border-top: 1px solid #EAE3D9; display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                <button type="button" id="btn-cancel-renewal" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">Đóng</button>
                <button type="button" id="btn-proceed-renewal" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm" style="background: linear-gradient(135deg, #C5A880 0%, #9C7A4A 100%); color: #FFF; border: none;" disabled>
                    Tiến Hành Gia Hạn
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('mayhen_renewal_modal');
                const btnClose = document.getElementById('btn-close-renewal-modal');
                const btnCancel = document.getElementById('btn-cancel-renewal');
                const btnProceed = document.getElementById('btn-proceed-renewal');
                const plansList = document.getElementById('renewal-plans-list');
                const invTitle = document.getElementById('renewal-inv-title');
                const invExpiry = document.getElementById('renewal-inv-expiry');
                const statusBadge = document.getElementById('renewal-status-badge');

                let currentInvitationId = null;
                let selectedPlanId = null;

                const closeModal = () => {
                    if (modal) modal.style.display = 'none';
                    selectedPlanId = null;
                    if (btnProceed) btnProceed.disabled = true;
                };

                if (btnClose) btnClose.addEventListener('click', closeModal);
                if (btnCancel) btnCancel.addEventListener('click', closeModal);
                if (modal) {
                    modal.addEventListener('click', (e) => {
                        if (e.target === modal) closeModal();
                    });
                }

                document.querySelectorAll('.btn-open-renewal-modal').forEach(btn => {
                    btn.addEventListener('click', async () => {
                        currentInvitationId = btn.getAttribute('data-invitation-id');
                        if (!currentInvitationId || !modal) return;

                        modal.style.display = 'flex';
                        plansList.innerHTML = '<div style="text-align: center; padding: 20px; color: #7A726A; font-style: italic;">Đang tải thông tin...</div>';

                        try {
                            const res = await fetch(`/tai-khoan/thiep/${currentInvitationId}/vong-doi`, {
                                headers: { 'Accept': 'application/json' }
                            });
                            const data = await res.json();

                            if (data && data.success && data.lifecycle) {
                                const lc = data.lifecycle;
                                if (invTitle) invTitle.innerText = data.title;
                                if (invExpiry) invExpiry.innerText = `Hạn dùng hiện tại: ${lc.expires_at_formatted}`;
                                if (statusBadge) {
                                    statusBadge.innerText = lc.status_label;
                                    statusBadge.className = `mayhen_badge ${lc.badge_class}`;
                                }

                                plansList.innerHTML = '';
                                if (lc.renewal_options && lc.renewal_options.length > 0) {
                                    lc.renewal_options.forEach((opt, idx) => {
                                        const card = document.createElement('label');
                                        card.style.cssText = 'display: flex; align-items: center; justify-content: space-between; border: 1px solid #EAE3D9; border-radius: 10px; padding: 12px 16px; background: #FFF; cursor: pointer; transition: all 0.2s ease;';
                                        card.innerHTML = `
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <input type="radio" name="renewal_plan" value="${opt.plan_id}" ${idx === 0 ? 'checked' : ''} style="accent-color: #C5A880; width: 18px; height: 18px; cursor: pointer;">
                                                <div>
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <strong style="font-size: 14px; color: #1F1D1A;">${opt.name}</strong>
                                                        <span style="font-size: 11px; font-weight: 600; padding: 2px 6px; border-radius: 4px; background: ${opt.is_upgrade ? '#FEF3C7' : '#EFE8DD'}; color: ${opt.is_upgrade ? '#B45309' : '#6B5E4B'};">${opt.type_label}</span>
                                                    </div>
                                                    <span style="font-size: 12px; color: #7A726A;">Thời lượng: +${opt.duration_label}</span>
                                                </div>
                                            </div>
                                            <strong style="font-size: 15px; color: #9C7A4A;">${opt.formatted_price}</strong>
                                        `;

                                        const radio = card.querySelector('input[type="radio"]');
                                        radio.addEventListener('change', () => {
                                            selectedPlanId = opt.plan_id;
                                            if (btnProceed) btnProceed.disabled = false;
                                        });

                                        if (idx === 0) {
                                            selectedPlanId = opt.plan_id;
                                            if (btnProceed) btnProceed.disabled = false;
                                        }

                                        plansList.appendChild(card);
                                    });
                                }
                            }
                        } catch (err) {
                            plansList.innerHTML = '<div style="color: #C62828; padding: 12px; text-align: center;">Không thể nạp thông tin. Vui lòng thử lại.</div>';
                        }
                    });
                });

                if (btnProceed) {
                    btnProceed.addEventListener('click', async () => {
                        if (!currentInvitationId || !selectedPlanId) return;

                        btnProceed.disabled = true;
                        btnProceed.innerText = 'Đang chuyển hướng...';

                        try {
                            const res = await fetch(`/tai-khoan/thiep/${currentInvitationId}/gia-han`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ plan_id: selectedPlanId })
                            });
                            const data = await res.json();

                            if (data && data.success && data.checkout_url) {
                                window.location.href = data.checkout_url;
                            } else {
                                alert(data?.message || 'Có lỗi xảy ra khi tạo yêu cầu.');
                                btnProceed.disabled = false;
                                btnProceed.innerText = 'Tiến Hành Gia Hạn';
                            }
                        } catch (err) {
                            alert('Có lỗi xảy ra. Vui lòng thử lại.');
                            btnProceed.disabled = false;
                            btnProceed.innerText = 'Tiến Hành Gia Hạn';
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection

