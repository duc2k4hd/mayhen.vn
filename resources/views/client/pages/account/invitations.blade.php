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
                    $coverPhoto = $wedding->cover_photo_url ?? ($inv->template->thumbnail_url ?? asset('assets/client/images/hero-default.webp'));
                    $isLocked = !empty($inv->custom_config['is_locked']);
                    $guestCount = $wedding ? $wedding->guests->count() : 0;
                @endphp
                <div class="mayhen_account_invitation_card">
                    <div class="mayhen_account_card_media">
                        <img src="{{ $coverPhoto }}" alt="{{ $groomName }} & {{ $brideName }}" class="mayhen_account_card_img" onerror="this.src='{{ asset('assets/client/images/hero-default.webp') }}'">
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

                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #8C827A;">
                            <span>Khách mời: <strong>{{ $guestCount }}</strong></span>
                            @if($inv->expires_at)
                                <span>Hết hạn: {{ $inv->expires_at->format('d/m/Y') }}</span>
                            @endif
                        </div>

                        <!-- Link thiệp công khai -->
                        <div style="background: #FAF8F5; border: 1px dashed #EAE3D9; border-radius: 8px; padding: 8px 10px; display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 12px;">
                            <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-family: monospace; color: #9C7A4A;">
                                {{ url('/thiep/' . $inv->slug) }}
                            </span>
                            <button type="button" class="mayhen_account_copy_btn" style="padding: 3px 8px; font-size: 11px;" onclick="navigator.clipboard.writeText('{{ url('/thiep/' . $inv->slug) }}'); this.innerText='Đã chép!'; setTimeout(() => this.innerText='Chép', 2000);">
                                Chép
                            </button>
                        </div>

                        <div class="mayhen_account_card_actions">
                            <a href="{{ route('invitation.create', ['edit' => $inv->slug]) }}" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 20h9"></path>
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                </svg>
                                <span>Vào Studio</span>
                            </a>

                            <a href="{{ route('guests.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                </svg>
                                <span>Khách Mời</span>
                            </a>

                            <a href="{{ route('account.wishes.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                                <span>Sổ Lưu Bút</span>
                            </a>

                            <a href="{{ route('account.gifts.index', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Quản lý tiền mừng cưới">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                    <line x1="2" y1="10" x2="22" y2="10"></line>
                                </svg>
                                <span>Sổ Mừng Cưới</span>
                            </a>

                            <a href="{{ route('photo_drop.manage', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Quản lý ảnh do khách mời chụp tại bàn tiệc">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                                <span>Ảnh Khách</span>
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

                            <a href="{{ route('account.invitations.settings', $inv->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" title="Cài đặt thiệp cưới">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                </svg>
                                <span>Cài Đặt</span>
                            </a>

                            <a href="{{ route('invitation.show', $inv->slug) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <span>Xem Thiệp</span>
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
@endsection

