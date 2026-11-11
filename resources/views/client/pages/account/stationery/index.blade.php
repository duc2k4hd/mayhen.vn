@extends('client.pages.account.layout')

@php
    $pageTitle = 'Bộ Ấn Phẩm & Thiệp Đồng Bộ';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/stationery.css') }}?v={{ time() }}">
@endpush

@section('account_breadcrumb', 'Bộ Ấn Phẩm Đồng Bộ')

@section('account_content')
    <div class="mayhen_stationery_header">
        <div class="mayhen_stationery_eyebrow">
            <span>✦</span>
            <span>Đề Án 78 • Bộ Thiệp Đồng Bộ (Mục 2.7 DOCX & Section 38 PRD V3)</span>
        </div>
        <h2 class="mayhen_stationery_title">
            Bộ Ấn Phẩm & Thiệp Cưới Đa Định Dạng
        </h2>
        <p class="mayhen_stationery_desc">
            Từ dữ liệu ngày chung đôi của {{ $wedding->groom_name }} & {{ $wedding->bride_name }}, Mây Hẹn tự động tạo trọn bộ ấn phẩm đồ họa chuẩn Quiet Luxury: Ảnh chia sẻ Story 9:16, Ảnh Save The Date, Standee QR để bàn tiệc, Lịch trình Timeline và Thiệp Báo Hỷ sắc nét, sẵn sàng tải về hoặc in ấn tức thời.
        </p>
    </div>

    {{-- Dữ liệu truyền sang Javascript qua data attributes an toàn --}}
    <div 
        id="mayhen_stationery_app"
        class="mayhen_stationery_container"
        data-wedding="{{ json_encode($stationeryData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-presets="{{ json_encode($presets, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-palettes="{{ json_encode($palettes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-log-url="{{ route('account.stationery.log_download', $invitation->id) }}"
        data-csrf="{{ csrf_token() }}"
    >
        {{-- Cột Trái: Bộ Điều Khiển Tùy Biến (Control Panel) --}}
        <div class="mayhen_stationery_controls">
            {{-- Chọn Loại Ấn Phẩm --}}
            <div class="mayhen_stationery_card">
                <div class="mayhen_stationery_card_header">
                    <span class="mayhen_stationery_step_badge">1</span>
                    <h3 class="mayhen_stationery_card_title">Chọn Định Dạng Ấn Phẩm</h3>
                </div>

                <div class="mayhen_stationery_presets_grid">
                    @foreach($presets as $key => $preset)
                        <button 
                            type="button" 
                            class="mayhen_stationery_preset_btn {{ $loop->first ? 'active' : '' }}"
                            data-preset-id="{{ $key }}"
                        >
                            <span class="mayhen_stationery_preset_icon">{{ $preset['icon'] }}</span>
                            <div class="mayhen_stationery_preset_info">
                                <strong class="mayhen_stationery_preset_name">{{ $preset['name'] }}</strong>
                                <span class="mayhen_stationery_preset_ratio">{{ $preset['ratio'] }} • {{ $preset['tag'] }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Chọn Bảng Màu Quiet Luxury --}}
            <div class="mayhen_stationery_card">
                <div class="mayhen_stationery_card_header">
                    <span class="mayhen_stationery_step_badge">2</span>
                    <h3 class="mayhen_stationery_card_title">Tông Màu Nhận Diện Hôn Lễ</h3>
                </div>

                <div class="mayhen_stationery_palettes_list">
                    @foreach($palettes as $key => $palette)
                        <button 
                            type="button" 
                            class="mayhen_stationery_palette_item {{ $loop->first ? 'active' : '' }}"
                            data-palette-id="{{ $key }}"
                        >
                            <div class="mayhen_stationery_palette_preview">
                                <span style="background-color: {{ $palette['card_bg'] }};"></span>
                                <span style="background-color: {{ $palette['accent'] }};"></span>
                                <span style="background-color: {{ $palette['bg'] }};"></span>
                            </div>
                            <span class="mayhen_stationery_palette_name">{{ $palette['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Tùy Biến Lời Nhắn Nhanh --}}
            <div class="mayhen_stationery_card">
                <div class="mayhen_stationery_card_header">
                    <span class="mayhen_stationery_step_badge">3</span>
                    <h3 class="mayhen_stationery_card_title">Tùy Chỉnh Thông Điệp</h3>
                </div>

                <div class="mayhen_stationery_form_group">
                    <label class="mayhen_stationery_label" for="stationery_custom_title">Tiêu Đề / Lời Tựa</label>
                    <input 
                        type="text" 
                        id="stationery_custom_title" 
                        class="mayhen_stationery_input" 
                        value="LỜI MỜI TRÂN QUÝ"
                    >
                </div>

                <div class="mayhen_stationery_form_group">
                    <label class="mayhen_stationery_label" for="stationery_custom_quote">Câu Trích Dẫn Yêu Thương</label>
                    <textarea 
                        id="stationery_custom_quote" 
                        class="mayhen_stationery_textarea" 
                        rows="2"
                    >{{ $stationeryData['quote'] }}</textarea>
                </div>

                <div class="mayhen_stationery_toggle_row">
                    <label class="mayhen_stationery_switch_label" for="stationery_show_qr">
                        <input type="checkbox" id="stationery_show_qr" class="mayhen_stationery_checkbox" checked>
                        <span>Hiển thị mã QR liên kết thiệp cưới online</span>
                    </label>
                </div>
            </div>

            {{-- Bộ Nút Xuất & Tải Ảnh --}}
            <div class="mayhen_stationery_actions_card">
                <button 
                    type="button" 
                    id="mayhen_stationery_download_btn" 
                    class="mayhen_account_btn mayhen_account_btn_gold mayhen_stationery_btn_block"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>Tải Ảnh PNG Độ Nét Cao (2K)</span>
                </button>

                <div class="mayhen_stationery_secondary_btns">
                    <button 
                        type="button" 
                        id="mayhen_stationery_copy_btn" 
                        class="mayhen_account_btn mayhen_account_btn_secondary"
                        title="Sao chép ảnh vào Clipboard để dán ngay vào Zalo/Messenger"
                    >
                        <span>📋 Sao Chép Ảnh</span>
                    </button>

                    <button 
                        type="button" 
                        id="mayhen_stationery_print_btn" 
                        class="mayhen_account_btn mayhen_account_btn_secondary"
                        title="In ấn theo khổ giấy chuẩn A4/A5"
                    >
                        <span>🖨️ In Ấn Chuẩn Khổ</span>
                    </button>
                </div>

                <div id="mayhen_stationery_toast" class="mayhen_stationery_toast" style="display: none;"></div>
            </div>
        </div>

        {{-- Cột Phải: Khung Xem Trước Trực Quan (Live Canvas Preview) --}}
        <div class="mayhen_stationery_preview_wrap">
            <div class="mayhen_stationery_preview_toolbar">
                <div class="mayhen_stationery_preview_title">
                    <span id="mayhen_preview_preset_label">Ảnh Chia Sẻ Story 9:16</span>
                    <span class="mayhen_stationery_badge_retina">Ultra HD • 2x Retina</span>
                </div>
                <div class="mayhen_stationery_preview_hint">
                    Tự động đồng bộ theo nội dung tùy chỉnh
                </div>
            </div>

            {{-- Khung chứa Canvas hiển thị --}}
            <div id="mayhen_canvas_frame" class="mayhen_stationery_canvas_frame">
                <canvas id="mayhen_stationery_canvas" class="mayhen_stationery_canvas"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/stationery.js') }}?v={{ time() }}"></script>
@endpush

