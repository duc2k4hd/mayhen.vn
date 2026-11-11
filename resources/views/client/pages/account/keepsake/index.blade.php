@extends('client.pages.account.layout')

@php
    $pageTitle = 'Lưu Giữ Kỷ Niệm Hậu Sự Kiện & Chế Độ Kỷ Niệm';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/keepsake.css') }}?v={{ time() }}">
@endpush

@section('account_breadcrumb', 'Lưu Giữ Kỷ Niệm')

@section('account_content')
    {{-- Header Banner --}}
    <div class="mayhen_keepsake_header">
        <div class="mayhen_keepsake_eyebrow">
            <span>✦</span>
            <span>Đề Án 75 • Lưu Giữ Kỷ Niệm & Memorial Mode</span>
        </div>
        <h2 class="mayhen_keepsake_title">
            Không Gian Kỷ Niệm Hôn Lễ: {{ $wedding->groom_name }} & {{ $wedding->bride_name }}
        </h2>
        <p class="mayhen_keepsake_desc">
            Sau ngày chung đôi ngập tràn cảm xúc, hãy chuyển đổi tấm thiệp cưới của bạn thành một trang lưu niệm vĩnh cửu. Gửi lời cảm ơn chân thành đến từng quan khách, chia sẻ video highlight ngày cưới và xuất cuốn Sổ Lưu Niệm truyền đời.
        </p>
    </div>

    @if(session('success'))
        <div style="background: #E8F5E9; border-left: 4px solid #2E7D32; color: #1B5E20; padding: 12px 18px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Mode Switch Card (Memorial Keepsake Mode) --}}
    <div class="mayhen_keepsake_switch_card">
        <div class="mayhen_keepsake_switch_info">
            <div class="mayhen_keepsake_switch_title">
                <span>Chế Độ Thiệp Kỷ Niệm (Memorial Mode)</span>
                <span id="mayhen_keepsake_status_badge" class="mayhen_keepsake_badge {{ $keepsake->is_enabled ? 'mayhen_keepsake_badge--active' : 'mayhen_keepsake_badge--inactive' }}">
                    {{ $keepsake->is_enabled ? 'Đang Bật' : 'Chưa Bật' }}
                </span>
            </div>
            <p class="mayhen_keepsake_switch_desc">
                Khi bật chế độ này, thiệp cưới công khai sẽ tự động ẩn form RSVP xác nhận tham dự, hiển thị bức tâm thư cảm ơn sâu sắc của uyên ương và video highlight ngày cưới.
            </p>
        </div>
        <div>
            <label class="mayhen_keepsake_toggle" title="Bật hoặc tắt Chế độ Thiệp Kỷ Niệm">
                <input 
                    type="checkbox" 
                    id="mayhen_keepsake_toggle_input" 
                    data-invitation-id="{{ $invitation->id }}" 
                    data-toggle-url="{{ route('account.keepsake.toggle', $invitation->id) }}"
                    {{ $keepsake->is_enabled ? 'checked' : '' }}
                >
                <span class="mayhen_keepsake_slider"></span>
            </label>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="mayhen_keepsake_kpis">
        <div class="mayhen_keepsake_kpi_card">
            <div class="mayhen_keepsake_kpi_label">Cột Mốc Hôn Lễ</div>
            <div class="mayhen_keepsake_kpi_val" style="font-size: 1.25rem;">
                {{ $stats['milestone_label'] }}
            </div>
            <div class="mayhen_keepsake_kpi_sub">
                {{ $stats['days_since'] >= 0 ? ($stats['days_since'] . ' ngày chung đôi') : 'Chưa tới ngày cưới' }}
            </div>
        </div>

        <div class="mayhen_keepsake_kpi_card">
            <div class="mayhen_keepsake_kpi_label">Quan Khách Điểm Danh</div>
            <div class="mayhen_keepsake_kpi_val">
                {{ number_format($stats['attended_guests_count']) }}
            </div>
            <div class="mayhen_keepsake_kpi_sub">Khách đã dự tiệc tại sảnh</div>
        </div>

        <div class="mayhen_keepsake_kpi_card">
            <div class="mayhen_keepsake_kpi_label">Lời Chúc Lưu Bút</div>
            <div class="mayhen_keepsake_kpi_val">
                {{ number_format($stats['wishes_count']) }}
            </div>
            <div class="mayhen_keepsake_kpi_sub">Lời chúc gửi qua thiệp số</div>
        </div>

        <div class="mayhen_keepsake_kpi_card">
            <div class="mayhen_keepsake_kpi_label">Khoảnh Khắc Bàn Tiệc</div>
            <div class="mayhen_keepsake_kpi_val">
                {{ number_format($stats['photos_count']) }}
            </div>
            <div class="mayhen_keepsake_kpi_sub">Ảnh quan khách đóng góp</div>
        </div>
    </div>

    {{-- Grid 2 Cột --}}
    <div class="mayhen_keepsake_grid">
        {{-- Cột Trái: Cấu hình Lời Cảm Ơn & Video Highlight --}}
        <div>
            <form action="{{ route('account.keepsake.update', $invitation->id) }}" method="POST" class="mayhen_keepsake_card">
                @csrf
                <div class="mayhen_keepsake_card_header">
                    <h3 class="mayhen_keepsake_card_title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9C7A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                        </svg>
                        <span>Cấu Hình Tâm Thư Cảm Ơn & Kỷ Niệm</span>
                    </h3>
                </div>

                <div class="mayhen_keepsake_form_group">
                    <label class="mayhen_keepsake_label" for="thank_you_title">Tiêu Đề Tâm Thư Cảm Ơn *</label>
                    <input 
                        type="text" 
                        id="thank_you_title" 
                        name="thank_you_title" 
                        class="mayhen_keepsake_input" 
                        value="{{ old('thank_you_title', $keepsake->thank_you_title) }}" 
                        required
                    >
                    <span class="mayhen_keepsake_help">Hiển thị trang trọng trên đầu thiệp cưới ở Chế độ Kỷ niệm.</span>
                </div>

                <div class="mayhen_keepsake_form_group">
                    <label class="mayhen_keepsake_label" for="thank_you_message">Nội Dung Lời Tri Ân Sâu Sắc *</label>
                    <textarea 
                        id="thank_you_message" 
                        name="thank_you_message" 
                        class="mayhen_keepsake_textarea" 
                        rows="6" 
                        required
                    >{{ old('thank_you_message', $keepsake->thank_you_message) }}</textarea>
                    <span class="mayhen_keepsake_help">Những lời nhắn gửi chân thành nhất của cô dâu chú rể tới gia đình, bạn bè sau hôn lễ.</span>
                </div>

                <div class="mayhen_keepsake_form_group">
                    <label class="mayhen_keepsake_label" for="video_url">Đường Dẫn Video Highlight Ngày Cưới (YouTube / Vimeo)</label>
                    <input 
                        type="url" 
                        id="video_url" 
                        name="video_url" 
                        class="mayhen_keepsake_input" 
                        placeholder="https://www.youtube.com/watch?v=..." 
                        value="{{ old('video_url', $keepsake->video_url) }}"
                    >
                    <span class="mayhen_keepsake_help">Video phóng sự cưới hoặc recap ngày trọng đại để bạn bè cùng nhìn lại khoảnh khắc đẹp.</span>
                </div>

                <div class="mayhen_keepsake_form_group">
                    <label class="mayhen_keepsake_label" for="anniversary_date">Ngày Kỷ Niệm Hôn Lễ</label>
                    <input 
                        type="date" 
                        id="anniversary_date" 
                        name="anniversary_date" 
                        class="mayhen_keepsake_input" 
                        value="{{ old('anniversary_date', $keepsake->anniversary_date ? \Carbon\Carbon::parse($keepsake->anniversary_date)->format('Y-m-d') : ($wedding->wedding_date ? \Carbon\Carbon::parse($wedding->wedding_date)->format('Y-m-d') : '')) }}"
                    >
                </div>

                <div class="mayhen_keepsake_form_group">
                    <label class="mayhen_keepsake_label">Tùy Chọn Hiển Thị Khi Kích Hoạt Kỷ Niệm</label>
                    <div class="mayhen_keepsake_checkbox_group">
                        <label class="mayhen_keepsake_checkbox_label">
                            <input type="checkbox" name="hide_rsvp" value="1" {{ $keepsake->hide_rsvp ? 'checked' : '' }}>
                            <span>Tự động ẩn form RSVP sau cưới</span>
                        </label>
                        <label class="mayhen_keepsake_checkbox_label">
                            <input type="checkbox" name="show_wishes" value="1" {{ $keepsake->show_wishes ? 'checked' : '' }}>
                            <span>Hiển thị lưu bút & lời chúc phúc</span>
                        </label>
                        <label class="mayhen_keepsake_checkbox_label">
                            <input type="checkbox" name="show_video" value="1" {{ $keepsake->show_video ? 'checked' : '' }}>
                            <span>Hiển thị video highlight hôn lễ</span>
                        </label>
                        <label class="mayhen_keepsake_checkbox_label">
                            <input type="checkbox" name="hide_gifts" value="1" {{ $keepsake->hide_gifts ? 'checked' : '' }}>
                            <span>Ẩn thông tin số tài khoản mừng cưới</span>
                        </label>
                    </div>
                </div>

                <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                    <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <span>Lưu Cấu Hình Kỷ Niệm</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Cột Phải: Xuất Sổ Lưu Niệm & Gửi Lời Cảm Ơn Từng Khách --}}
        <div>
            {{-- Xuất Sổ Lưu Niệm Card --}}
            <div class="mayhen_keepsake_card">
                <div class="mayhen_keepsake_card_header">
                    <h3 class="mayhen_keepsake_card_title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9C7A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                        </svg>
                        <span>Sổ Lưu Niệm Ngày Cưới</span>
                    </h3>
                </div>
                <p style="font-size: 0.85rem; color: #7A726A; line-height: 1.6; margin-bottom: 16px;">
                    Tổng hợp toàn bộ lời chúc phúc ấm áp, tâm thư cảm ơn của hai bạn và danh sách quan khách vào cuốn Sổ Lưu Niệm định dạng trang nhã sẵn sàng in ấn khổ A4 hoặc lưu trữ PDF kỷ niệm trọn đời.
                </p>
                <a href="{{ route('account.keepsake.export', $invitation->id) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_gold" style="width: 100%; justify-content: center; box-sizing: border-box;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    <span>Mở & In Sổ Lưu Niệm (A4 / PDF)</span>
                </a>
            </div>

            {{-- Gửi Lời Cảm Ơn Cá Nhân Hóa Card --}}
            <div class="mayhen_keepsake_card">
                <div class="mayhen_keepsake_card_header">
                    <h3 class="mayhen_keepsake_card_title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9C7A4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <span>Tin Nhắn Cảm Ơn Quan Khách</span>
                    </h3>
                </div>
                <p style="font-size: 0.82rem; color: #7A726A; line-height: 1.5; margin-bottom: 14px;">
                    Mỗi quan khách đã tới dự đều mang một dấu ấn riêng. Nhấp vào khách mời để lấy mẫu tin nhắn cảm ơn trang trọng đính kèm link xem lại kỷ niệm thiệp cưới để gửi qua Zalo / SMS.
                </p>

                @if($guests->isEmpty())
                    <div style="text-align: center; padding: 24px; color: #7A726A; font-size: 0.85rem; background: #FAF8F5; border-radius: 8px;">
                        Chưa có khách mời nào được ghi nhận tham dự hoặc check-in.
                    </div>
                @else
                    <div style="max-height: 380px; overflow-y: auto; border: 1px solid #EFE8DD; border-radius: 8px;">
                        @foreach($guests as $guest)
                            <div class="mayhen_keepsake_guest_item">
                                <div>
                                    <span class="mayhen_keepsake_guest_name">
                                        {{ $guest->salutation ? ($guest->salutation . ' ') : '' }}{{ $guest->name }}
                                    </span>
                                    <span class="mayhen_keepsake_guest_sub">
                                        {{ $guest->phone ? $guest->phone : 'Khách mời' }}
                                        @if($guest->isCheckedIn())
                                            • <strong style="color: #2E7D32;">✓ Đã Điểm Danh</strong>
                                        @endif
                                    </span>
                                </div>
                                <button 
                                    type="button" 
                                    class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm mayhen_keepsake_btn_thank_you"
                                    data-guest-id="{{ $guest->id }}"
                                    data-url="{{ route('account.keepsake.thank_you_message', [$invitation->id, $guest->id]) }}"
                                >
                                    Lời Cảm Ơn
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div style="margin-top: 10px;">
                        {{ $guests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Lời Cảm Ơn Cá Nhân Hóa Từng Khách Mời --}}
    <div id="mayhen_keepsake_modal" class="mayhen_keepsake_modal">
        <div class="mayhen_keepsake_modal_box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #EFE8DD; padding-bottom: 10px;">
                <h4 style="font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; margin: 0; color: #1F1D1A;">
                    Gửi Lời Cảm Ơn Tới: <span id="mayhen_modal_guest_name" style="color: #9C7A4A;"></span>
                </h4>
                <button type="button" class="mayhen_modal_close" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #7A726A;">&times;</button>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                    Nội Dung Tin Nhắn Đã Chuẩn Hóa Theo Danh Xưng & Link Kỷ Niệm:
                </label>
                <textarea id="mayhen_modal_message_text" class="mayhen_keepsake_textarea" rows="8" readonly style="background: #FAF8F5; font-size: 0.85rem;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="mayhen_account_btn mayhen_account_btn_outline mayhen_modal_close">
                    Đóng
                </button>
                <button type="button" id="mayhen_modal_copy_btn" class="mayhen_account_btn mayhen_account_btn_gold">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    <span>Sao Chép Tin Nhắn</span>
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/keepsake.js') }}?v={{ time() }}"></script>
@endpush
