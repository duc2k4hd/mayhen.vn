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

                            <a href="{{ route('account.share.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Bộ công cụ chia sẻ Zalo & thẻ xem trước OpenGraph">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="18" cy="5" r="3"></circle>
                                    <circle cx="6" cy="12" r="3"></circle>
                                    <circle cx="18" cy="19" r="3"></circle>
                                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                                </svg>
                                <span>Chia Sẻ Zalo</span>
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

                            {{-- Nút Nhân Bản Nhà Trai / Nhà Gái & Phân Tách Lễ Vu Quy - Tân Hôn (Mục 2.2 DOCX & Đề Án 125) --}}
                            <button type="button" 
                                class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm btn-open-ceremony-fork-modal"
                                data-invitation-id="{{ $inv->id }}"
                                data-title="{{ $inv->title ?? 'Thiệp Cưới' }}"
                                data-groom="{{ $groomName }}"
                                data-bride="{{ $brideName }}"
                                data-options-url="{{ route('account.ceremony.fork_options', $inv->id) }}"
                                data-fork-url="{{ route('account.ceremony.fork', $inv->id) }}"
                                title="Nhân bản thiệp cưới riêng cho Nhà Trai (Lễ Thành Hôn) hoặc Nhà Gái (Lễ Vu Quy)">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                </svg>
                                <span>Nhân Bản Vu Quy / Tân Hôn</span>
                            </button>

                            {{-- Nút Cấu Hình Đa Ngôn Ngữ Thiệp Cưới (Mục 2.10 DOCX & Đề Án 126) --}}
                            <a href="{{ route('account.multilingual.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Cấu hình đa ngôn ngữ thiệp cưới: Tiếng Việt, Anh, Hàn, Trung">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="2" y1="12" x2="22" y2="12"></line>
                                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                </svg>
                                <span>Đa Ngôn Ngữ</span>
                            </a>

                            {{-- Nút Báo Cáo Hộ Gia Đình & Bếp Tiệc (Mục 2.4 DOCX & Đề Án 127) --}}
                            <a href="{{ route('account.household.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Báo cáo khách mời hộ gia đình, suất ăn người lớn/trẻ em & bảng kê Bếp trưởng">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span>Bếp Tiệc &amp; Hộ Gia Đình</span>
                            </a>

                            {{-- Nút Thiệp Theo Nhóm Khách (Mục 2.3 DOCX & Đề Án 128) --}}
                            <a href="{{ route('account.smart_groups.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Phân tuyến sự kiện, lịch trình & lời mời riêng cho từng nhóm khách">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                                    <polyline points="2 17 12 22 22 17"></polyline>
                                    <polyline points="2 12 12 17 22 12"></polyline>
                                </svg>
                                <span>Thiệp Theo Nhóm</span>
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

                            {{-- Nút Xuất & Tải Trọn Bộ Kỷ Niệm Cưới .ZIP (Mục 6.5 & 13.4 DOCX) --}}
                            <a href="{{ route('account.invitations.download_archive', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" style="width: 100%; margin-top: 6px; border-color: #E2D9CF; color: #5C554D;" title="Tải toàn bộ dữ liệu kỷ niệm: CSV khách, lời chúc, JSON và trang kỷ niệm offline">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                <span>Tải Kỷ Niệm (.ZIP)</span>
                            </a>
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

    {{-- Modal Nhân Bản Thiệp Cưới & Phân Tách Lễ Vu Quy - Tân Hôn (Mục 2.2 DOCX & Đề Án 125) --}}
    <div id="mayhen_ceremony_fork_modal" style="display: none; position: fixed; inset: 0; background: rgba(31, 29, 26, 0.7); backdrop-filter: blur(6px); z-index: 99999; align-items: center; justify-content: center; padding: 16px;">
        <div style="background: #FFFFFF; border-radius: 18px; width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 50px rgba(0,0,0,0.25); display: flex; flex-direction: column;">
            <div style="padding: 18px 24px; border-bottom: 1px solid #EAE3D9; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 20px;">🌸👑</span>
                    <div>
                        <h3 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; font-weight: 700; color: #1F1D1A; margin: 0;">Nhân Bản Lễ Cưới & Phân Nhánh</h3>
                        <span style="font-size: 12px; color: #7A726A;">Nhà Trai (Thành Hôn) • Nhà Gái (Vu Quy) • Tiệc Báo Hỷ</span>
                    </div>
                </div>
                <button type="button" id="btn-close-fork-modal" style="background: none; border: none; font-size: 20px; color: #7A726A; cursor: pointer; padding: 4px;">✕</button>
            </div>

            <form id="mayhen_fork_form" style="display: flex; flex-direction: column; margin: 0;">
                <div style="padding: 20px 24px; display: flex; flex-direction: column; gap: 16px;">
                    {{-- Thiệp gốc tóm tắt --}}
                    <div style="background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 12px; padding: 12px 16px;">
                        <span style="font-size: 11px; letter-spacing: 1px; color: #9C7A4A; font-weight: 700; text-transform: uppercase;">Thiệp Gốc Được Chọn</span>
                        <div id="fork-modal-source-title" style="font-size: 14px; font-weight: 600; color: #1F1D1A; margin-top: 2px;">...</div>
                        <div id="fork-modal-source-meta" style="font-size: 12px; color: #7A726A; margin-top: 2px;">...</div>
                    </div>

                    {{-- Danh sách 4 chế độ --}}
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 8px;">
                            Chọn Chế Độ Nhân Bản Phù Hợp Phong Tục:
                        </label>
                        <div id="fork-modes-container" style="display: flex; flex-direction: column; gap: 8px;">
                            {{-- Sẽ được nạp động hoặc fallback sẵn --}}
                            <label class="mayhen_fork_mode_card" style="display: flex; align-items: flex-start; gap: 12px; border: 1.5px solid #C5A880; border-radius: 10px; padding: 12px 14px; background: #FAF7F2; cursor: pointer;">
                                <input type="radio" name="fork_mode" value="bride_side" checked style="margin-top: 3px; accent-color: #9C7A4A; width: 17px; height: 17px;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <strong style="font-size: 14px; color: #1F1D1A;">🌸 Nhà Gái — Lễ Vu Quy</strong>
                                        <span style="font-size: 11px; background: #FCE7F3; color: #9D174D; padding: 2px 8px; border-radius: 12px; font-weight: 600;">Khuyên Dùng</span>
                                    </div>
                                    <p style="font-size: 12px; color: #665E55; margin: 4px 0 0 0; line-height: 1.4;">
                                        Ưu tiên hiển thị nhà gái & cô dâu trước, đổi tiêu đề thành Lễ Vu Quy, cấu hình giờ tiệc và tài khoản mừng cưới bên nhà gái.
                                    </p>
                                </div>
                            </label>

                            <label class="mayhen_fork_mode_card" style="display: flex; align-items: flex-start; gap: 12px; border: 1px solid #EAE3D9; border-radius: 10px; padding: 12px 14px; background: #FFF; cursor: pointer;">
                                <input type="radio" name="fork_mode" value="groom_side" style="margin-top: 3px; accent-color: #9C7A4A; width: 17px; height: 17px;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <strong style="font-size: 14px; color: #1F1D1A;">👑 Nhà Trai — Lễ Thành Hôn</strong>
                                        <span style="font-size: 11px; background: #FEF3C7; color: #92400E; padding: 2px 8px; border-radius: 12px; font-weight: 600;">Phổ Biến</span>
                                    </div>
                                    <p style="font-size: 12px; color: #665E55; margin: 4px 0 0 0; line-height: 1.4;">
                                        Ưu tiên hiển thị nhà trai & chú rể trước, đổi tiêu đề thành Lễ Thành Hôn, cấu hình giờ tiệc và tài khoản mừng cưới bên nhà trai.
                                    </p>
                                </div>
                            </label>

                            <label class="mayhen_fork_mode_card" style="display: flex; align-items: flex-start; gap: 12px; border: 1px solid #EAE3D9; border-radius: 10px; padding: 12px 14px; background: #FFF; cursor: pointer;">
                                <input type="radio" name="fork_mode" value="party_side" style="margin-top: 3px; accent-color: #9C7A4A; width: 17px; height: 17px;">
                                <div style="flex: 1;">
                                    <strong style="font-size: 14px; color: #1F1D1A;">🕊️ Tiệc Báo Hỷ — Bạn Hữu</strong>
                                    <p style="font-size: 12px; color: #665E55; margin: 4px 0 0 0; line-height: 1.4;">
                                        Dành cho tiệc thân mật cùng bạn bè, đồng nghiệp tại nơi làm việc/sinh sống xa quê hương.
                                    </p>
                                </div>
                            </label>

                            <label class="mayhen_fork_mode_card" style="display: flex; align-items: flex-start; gap: 12px; border: 1px solid #EAE3D9; border-radius: 10px; padding: 12px 14px; background: #FFF; cursor: pointer;">
                                <input type="radio" name="fork_mode" value="backup_copy" style="margin-top: 3px; accent-color: #9C7A4A; width: 17px; height: 17px;">
                                <div style="flex: 1;">
                                    <strong style="font-size: 14px; color: #1F1D1A;">📋 Bản Sao Lưu Dự Phòng</strong>
                                    <p style="font-size: 12px; color: #665E55; margin: 4px 0 0 0; line-height: 1.4;">
                                        Tạo bản sao lưu nguyên trạng toàn bộ thông tin để thử nghiệm giao diện hoặc chỉnh sửa an toàn.
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Tùy chọn nâng cao --}}
                    <div style="border-top: 1px dashed #EAE3D9; padding-top: 14px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px; font-weight: 600; color: #1F1D1A;">
                            <input type="checkbox" id="fork-separate-project" name="separate_project" value="1" checked style="accent-color: #9C7A4A; width: 17px; height: 17px;">
                            <span>Tách thành dự án riêng để quản lý khách mời, RSVP & xếp bàn độc lập</span>
                        </label>
                        <p style="font-size: 11px; color: #8C827A; margin: 4px 0 0 27px; line-height: 1.4;">
                            Giúp gia đình hai bên quản lý danh sách khách và thống kê tiền mừng hoàn toàn riêng tư mà không bị lẫn lộn dữ liệu.
                        </p>
                    </div>

                    {{-- Form tùy biến địa chỉ & ngày giờ riêng --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #4A433C; margin-bottom: 4px;">Địa điểm tiệc (Tùy chọn):</label>
                            <input type="text" id="fork-venue-name" name="venue_name" placeholder="VD: Trung tâm Tiệc Cưới White Palace" style="width: 100%; padding: 8px 12px; border: 1px solid #D9D2C7; border-radius: 8px; font-size: 12px; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #4A433C; margin-bottom: 4px;">Ngày tổ chức (Tùy chọn):</label>
                            <input type="date" id="fork-wedding-date" name="wedding_date" style="width: 100%; padding: 8px 12px; border: 1px solid #D9D2C7; border-radius: 8px; font-size: 12px; box-sizing: border-box;">
                        </div>
                    </div>
                </div>

                <div style="padding: 16px 24px; background: #FAF8F5; border-top: 1px solid #EAE3D9; display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                    <button type="button" id="btn-cancel-fork" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">Hủy Bỏ</button>
                    <button type="submit" id="btn-submit-fork" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm" style="background: linear-gradient(135deg, #C5A880 0%, #9C7A4A 100%); color: #FFF; border: none;">
                        Xác Nhận Nhân Bản
                    </button>
                </div>
            </form>
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

                // Xử lý Modal Nhân Bản Lễ Cưới & Phân Nhánh (Đề Án 125)
                const forkModal = document.getElementById('mayhen_ceremony_fork_modal');
                const btnCloseFork = document.getElementById('btn-close-fork-modal');
                const btnCancelFork = document.getElementById('btn-cancel-fork');
                const forkForm = document.getElementById('mayhen_fork_form');
                const btnSubmitFork = document.getElementById('btn-submit-fork');
                const forkSourceTitle = document.getElementById('fork-modal-source-title');
                const forkSourceMeta = document.getElementById('fork-modal-source-meta');
                const forkVenueName = document.getElementById('fork-venue-name');
                const forkWeddingDate = document.getElementById('fork-wedding-date');

                let currentForkUrl = null;

                const closeForkModal = () => {
                    if (forkModal) forkModal.style.display = 'none';
                    if (btnSubmitFork) {
                        btnSubmitFork.disabled = false;
                        btnSubmitFork.innerText = 'Xác Nhận Nhân Bản';
                    }
                };

                if (btnCloseFork) btnCloseFork.addEventListener('click', closeForkModal);
                if (btnCancelFork) btnCancelFork.addEventListener('click', closeForkModal);
                if (forkModal) {
                    forkModal.addEventListener('click', (e) => {
                        if (e.target === forkModal) closeForkModal();
                    });
                }

                document.querySelectorAll('.btn-open-ceremony-fork-modal').forEach(btn => {
                    btn.addEventListener('click', async () => {
                        const invId = btn.getAttribute('data-invitation-id');
                        const title = btn.getAttribute('data-title');
                        const groom = btn.getAttribute('data-groom');
                        const bride = btn.getAttribute('data-bride');
                        const optionsUrl = btn.getAttribute('data-options-url');
                        currentForkUrl = btn.getAttribute('data-fork-url');

                        if (!invId || !forkModal) return;

                        if (forkSourceTitle) forkSourceTitle.innerText = title;
                        if (forkSourceMeta) forkSourceMeta.innerText = `Cặp đôi: ${groom} & ${bride}`;

                        forkModal.style.display = 'flex';

                        // Highlight radio cards khi click
                        const modeCards = forkModal.querySelectorAll('.mayhen_fork_mode_card');
                        modeCards.forEach(card => {
                            card.addEventListener('click', () => {
                                modeCards.forEach(c => {
                                    c.style.borderColor = '#EAE3D9';
                                    c.style.background = '#FFF';
                                });
                                card.style.borderColor = '#C5A880';
                                card.style.background = '#FAF7F2';
                            });
                        });

                        // Nạp options từ backend nếu cần
                        try {
                            const res = await fetch(optionsUrl, {
                                headers: { 'Accept': 'application/json' }
                            });
                            const data = await res.json();
                            if (data && data.success && data.wedding) {
                                if (forkWeddingDate && data.wedding.wedding_date) {
                                    forkWeddingDate.value = data.wedding.wedding_date;
                                }
                                if (forkVenueName && data.wedding.venue_name) {
                                    forkVenueName.placeholder = `Gốc: ${data.wedding.venue_name}`;
                                }
                            }
                        } catch (e) {
                            // Im lặng dùng fallback
                        }
                    });
                });

                if (forkForm) {
                    forkForm.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        if (!currentForkUrl || !btnSubmitFork) return;

                        btnSubmitFork.disabled = true;
                        btnSubmitFork.innerText = 'Đang nhân bản...';

                        const formData = new FormData(forkForm);
                        const selectedMode = formData.get('fork_mode') || 'bride_side';
                        const separateProject = formData.get('separate_project') ? 1 : 0;
                        const venueName = formData.get('venue_name') || '';
                        const weddingDate = formData.get('wedding_date') || '';

                        try {
                            const res = await fetch(currentForkUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    mode: selectedMode,
                                    separate_project: separateProject,
                                    venue_name: venueName,
                                    wedding_date: weddingDate
                                })
                            });

                            const data = await res.json();
                            if (data && data.success) {
                                alert(data.message || 'Đã nhân bản thiệp cưới thành công!');
                                window.location.href = data.redirect_url || window.location.href;
                            } else {
                                alert(data?.message || 'Có lỗi xảy ra trong quá trình nhân bản.');
                                btnSubmitFork.disabled = false;
                                btnSubmitFork.innerText = 'Xác Nhận Nhân Bản';
                            }
                        } catch (err) {
                            alert('Không thể kết nối đến máy chủ. Vui lòng thử lại.');
                            btnSubmitFork.disabled = false;
                            btnSubmitFork.innerText = 'Xác Nhận Nhân Bản';
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection

