@extends('client.pages.account.layout')

@php
    $pageTitle = 'Bộ Công Cụ Chia Sẻ Zalo & Mạng Xã Hội';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/share.css') }}?v={{ time() }}">
@endpush

@section('account_breadcrumb', 'Chia Sẻ Thiệp Cưới & Zalo')

@section('account_content')
    <div class="mayhen_share_header">
        <div class="mayhen_share_eyebrow">
            <span>✦</span>
            <span>Mục 2.5 Master Prompt & Section 27, 30 PRD V3</span>
        </div>
        <h2 class="mayhen_share_title">
            Bộ Công Cụ Chia Sẻ Zalo &amp; Phân Phối Thiệp Cưới
        </h2>
        <p class="mayhen_share_desc">
            Gửi gắm lời mời trân quý của {{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }} đến người thân, bạn bè qua Zalo, Messenger, SMS với thẻ xem trước OpenGraph 1200x630px tuyệt mỹ và kịch bản tin nhắn chuẩn mực văn hóa cưới Việt Nam.
        </p>
    </div>

    {{-- Vùng dữ liệu khởi tạo cho JS --}}
    <div 
        id="mayhen_share_app"
        class="mayhen_share_container"
        data-invitation-id="{{ $invitation->id }}"
        data-invitation-url="{{ $invitationUrl }}"
        data-share-card-url="{{ $shareCardUrl }}"
        data-compose-url="{{ route('account.share.compose', $invitation->id) }}"
        data-log-url="{{ route('account.share.log', $invitation->id) }}"
        data-csrf="{{ csrf_token() }}"
    >
        {{-- Cột Trái: Trình Biên Soạn Tin Nhắn (Message Composer) --}}
        <div class="mayhen_share_panel">
            {{-- Bước 1: Chọn Kịch Bản Lời Mời Mẫu --}}
            <div class="mayhen_share_card">
                <div class="mayhen_share_card_header">
                    <span class="mayhen_share_step_badge">1</span>
                    <h3 class="mayhen_share_card_title">Chọn Kịch Bản Lời Mời Chuẩn Mực</h3>
                </div>

                <div class="mayhen_share_templates_grid">
                    @foreach($templates as $key => $tmpl)
                        <button 
                            type="button" 
                            class="mayhen_share_template_btn {{ $loop->first ? 'active' : '' }}"
                            data-template-key="{{ $key }}"
                        >
                            <span class="mayhen_share_template_icon">{{ $tmpl['icon'] }}</span>
                            <div class="mayhen_share_template_info">
                                <strong class="mayhen_share_template_name">{{ $tmpl['name'] }}</strong>
                                <span class="mayhen_share_template_audience">{{ $tmpl['audience'] }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Bước 2: Cá Nhân Hóa Tên Khách & Danh Xưng --}}
            <div class="mayhen_share_card">
                <div class="mayhen_share_card_header">
                    <span class="mayhen_share_step_badge">2</span>
                    <h3 class="mayhen_share_card_title">Cá Nhân Hóa Khách Mời</h3>
                </div>

                <div class="mayhen_share_form_row">
                    <div class="mayhen_share_form_col" style="flex: 1;">
                        <label class="mayhen_share_label" for="share_salutation">Danh Xưng</label>
                        <select id="share_salutation" class="mayhen_share_select">
                            <option value="Anh">Anh</option>
                            <option value="Chị">Chị</option>
                            <option value="Bạn" selected>Bạn</option>
                            <option value="Em">Em</option>
                            <option value="Gia đình">Gia đình</option>
                            <option value="Chú">Chú</option>
                            <option value="Bác">Bác</option>
                            <option value="Cô">Cô</option>
                            <option value="Dì">Dì</option>
                        </select>
                    </div>

                    <div class="mayhen_share_form_col" style="flex: 2;">
                        <label class="mayhen_share_label" for="share_guest_name">Tên Khách Mời</label>
                        <input 
                            type="text" 
                            id="share_guest_name" 
                            class="mayhen_share_input" 
                            placeholder="Ví dụ: Hoàng Nam, Thu Trang..."
                            value="Hoàng Nam"
                        >
                    </div>
                </div>

                @if($guests->isNotEmpty())
                    <div class="mayhen_share_quick_guests">
                        <span class="mayhen_share_quick_guests_label">Khách trong danh bạ:</span>
                        <div class="mayhen_share_quick_chips">
                            @foreach($guests->take(6) as $g)
                                <button 
                                    type="button" 
                                    class="mayhen_share_chip"
                                    data-guest-id="{{ $g->id }}"
                                    data-salutation="{{ $g->salutation ?: 'Bạn' }}"
                                    data-name="{{ $g->name }}"
                                >
                                    {{ $g->salutation }} {{ $g->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Bước 3: Nội Dung Tin Nhắn Đã Soạn --}}
            <div class="mayhen_share_card">
                <div class="mayhen_share_card_header">
                    <span class="mayhen_share_step_badge">3</span>
                    <h3 class="mayhen_share_card_title">Xem &amp; Hiệu Chỉnh Lời Nhắn</h3>
                </div>

                <div class="mayhen_share_textarea_wrap">
                    <textarea 
                        id="share_message_content" 
                        class="mayhen_share_textarea" 
                        rows="7"
                    >{{ $templates['formal']['content'] }}</textarea>
                </div>

                {{-- Nút Hành Động 1-Chạm --}}
                <div class="mayhen_share_actions_group">
                    <a 
                        href="https://zalo.me/share?url={{ urlencode($invitationUrl) }}" 
                        id="mayhen_share_zalo_btn" 
                        class="mayhen_account_btn mayhen_share_btn_zalo" 
                        target="_blank" 
                        rel="noopener noreferrer"
                    >
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 2C6.48 2 2 6.03 2 11c0 2.87 1.5 5.43 3.84 7.08L5 22l4.24-1.42c.87.27 1.8.42 2.76.42 5.52 0 10-4.03 10-9s-4.48-9-10-9zm1 14.5h-2v-2h2v2zm0-4h-2V7h2v5.5z"/>
                        </svg>
                        <span>Mở Zalo Gửi Ngay</span>
                    </a>

                    <button 
                        type="button" 
                        id="mayhen_share_copy_btn" 
                        class="mayhen_account_btn mayhen_account_btn_gold"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        <span>Sao Chép Lời Mời Kèm Link</span>
                    </button>
                </div>

                <div class="mayhen_share_sub_actions">
                    <button type="button" id="mayhen_share_native_btn" class="mayhen_account_btn mayhen_account_btn_secondary">
                        <span>📲 Chia Sẻ Qua App Khác</span>
                    </button>

                    <a href="{{ $shareCardUrl }}" download="the-chia-se-{{ $invitation->slug }}.svg" class="mayhen_account_btn mayhen_account_btn_secondary">
                        <span>📥 Tải Thẻ Card SVG (1200x630)</span>
                    </a>
                </div>

                <div id="mayhen_share_toast" class="mayhen_share_toast" style="display: none;"></div>
            </div>
        </div>

        {{-- Cột Phải: Xem Trước Thẻ Card & Tin Nhắn Thực Tế (Live Social Preview) --}}
        <div class="mayhen_share_preview_pane">
            <div class="mayhen_share_preview_box">
                <div class="mayhen_share_preview_label">
                    <span>Thẻ Xem Trước Mạng Xã Hội (OpenGraph Card 1200x630)</span>
                    <span class="mayhen_share_pill">Tự động sinh vector</span>
                </div>

                <div class="mayhen_share_card_frame">
                    <img 
                        src="{{ $shareCardUrl }}?v={{ time() }}" 
                        alt="Thẻ Card OpenGraph Chia Sẻ Thiệp Cưới" 
                        class="mayhen_share_card_img"
                        loading="eager"
                    >
                </div>

                <div class="mayhen_share_preview_tip">
                    <span>💡 Khi bạn dán liên kết thiệp vào Zalo, Facebook, Messenger hay iMessage, thẻ card sang trọng trên sẽ tự động hiển thị nguyên bản mà không bị méo hình.</span>
                </div>
            </div>

            {{-- Mô Phỏng Khung Chat Zalo Thực Tế --}}
            <div class="mayhen_share_chat_mockup">
                <div class="mayhen_share_chat_header">
                    <div class="mayhen_share_chat_avatar">Z</div>
                    <div class="mayhen_share_chat_meta">
                        <strong class="mayhen_share_chat_receiver" id="mockup_receiver_name">Hoàng Nam</strong>
                        <span class="mayhen_share_chat_status">Vừa truy cập</span>
                    </div>
                </div>

                <div class="mayhen_share_chat_body">
                    <div class="mayhen_share_bubble">
                        <p class="mayhen_share_bubble_text" id="mockup_bubble_text">
                            {!! nl2br(e($templates['formal']['content'])) !!}
                        </p>
                        <div class="mayhen_share_bubble_card">
                            <img src="{{ $shareCardUrl }}?v={{ time() }}" alt="Card Thumbnail" class="mayhen_share_bubble_card_img">
                            <div class="mayhen_share_bubble_card_info">
                                <span class="mayhen_share_bubble_card_title">{{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}</span>
                                <span class="mayhen_share_bubble_card_sub">Thiệp cưới online tinh tế • mayhen.vn</span>
                            </div>
                        </div>
                        <span class="mayhen_share_bubble_time">11:30 • Đã nhận</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/share.js') }}?v={{ time() }}"></script>
@endpush
