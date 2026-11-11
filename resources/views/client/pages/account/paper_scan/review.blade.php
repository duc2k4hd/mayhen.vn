@extends('client.pages.account.layout')

@php
    $pageTitle = 'Rà Soát Dữ Liệu Thiệp Giấy (Human-in-the-Loop)';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/paper_scan.css') }}?v={{ time() }}">
@endpush

@section('account_breadcrumb')
    <a href="{{ route('account.paper_scan.create', $invitation->id) }}">Quét Thiệp Giấy</a>
    <span class="mayhen_account_breadcrumbs_sep">/</span>
    <span>Rà Soát Thông Tin</span>
@endsection

@section('account_content')
    <div class="mayhen_paperscan_header">
        <div class="mayhen_paperscan_eyebrow">
            <span>✦</span>
            <span>Human-in-the-Loop Review • Bước Rà Soát Bắt Buộc</span>
        </div>
        <h2 class="mayhen_paperscan_title">
            Đối Chiếu & Xác Nhận Dữ Liệu Bóc Tách
        </h2>
        <p class="mayhen_paperscan_desc">
            Theo nguyên tắc bảo mật và thẩm mỹ của Mây Hẹn, dữ liệu do AI đọc được luôn cần sự xác nhận từ bạn để đảm bảo không sai lệch họ tên phụ mẫu, ngày giờ và thánh đường tổ chức trước khi áp dụng vào thiệp cưới số.
        </p>
    </div>

    @if(session('success'))
        <div style="background: #E8F5E9; border-left: 4px solid #2E7D32; color: #1B5E20; padding: 12px 18px; border-radius: 6px; margin-bottom: 20px; font-size: 0.88rem;">
            {{ session('success') }}
        </div>
    @endif

    <div class="mayhen_paperscan_review_grid">
        {{-- Cột Trái: Ảnh Thiệp Giấy Gốc & Công Cụ Kính Lúp --}}
        <div class="mayhen_paperscan_img_panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; color: #1F1D1A;">
                    Ảnh Thiệp Giấy Đối Chiếu
                </span>
                <span style="font-size: 0.75rem; color: #7A726A;">
                    Độ tin cậy: <strong style="color: #9C7A4A;">{{ $scan->confidence_score }}%</strong>
                </span>
            </div>

            <div class="mayhen_paperscan_img_container">
                <img 
                    id="mayhen_review_target_img" 
                    src="{{ $scan->image_url }}" 
                    alt="Ảnh thiệp giấy" 
                    class="mayhen_paperscan_target_img"
                >
            </div>

            <div class="mayhen_paperscan_img_toolbar">
                <div style="display: flex; gap: 6px;">
                    <button type="button" id="mayhen_zoom_in_btn" class="mayhen_paperscan_btn_tool" title="Phóng to">🔍 +</button>
                    <button type="button" id="mayhen_zoom_out_btn" class="mayhen_paperscan_btn_tool" title="Thu nhỏ">🔍 -</button>
                    <button type="button" id="mayhen_zoom_reset_btn" class="mayhen_paperscan_btn_tool" title="Đặt lại">100%</button>
                </div>
                <div>
                    <button type="button" id="mayhen_rotate_btn" class="mayhen_paperscan_btn_tool" title="Xoay ảnh 90 độ">🔄 Xoay</button>
                </div>
            </div>
            <span class="mayhen_paperscan_help" style="margin-top: 8px;">
                Bạn có thể cuộn chuột hoặc dùng các nút công cụ để phóng to đọc rõ các dòng chữ nhỏ trên thiệp.
            </span>
        </div>

        {{-- Cột Phải: Form Dữ Liệu Rà Soát & Hoàn Tất --}}
        <div class="mayhen_paperscan_form_panel">
            <div class="mayhen_paperscan_confidence_badge {{ $scan->confidence_score >= 80 ? 'mayhen_paperscan_confidence_badge--high' : 'mayhen_paperscan_confidence_badge--medium' }}">
                <span>✓</span>
                <span>Mức Độ Tin Cậy AI: {{ $scan->confidence_label }} ({{ $scan->confidence_score }}%)</span>
            </div>

            <form action="{{ route('account.paper_scan.apply', [$invitation->id, $scan->id]) }}" method="POST">
                @csrf

                {{-- Phần 1: Đôi Uyên Ương --}}
                <div class="mayhen_paperscan_section_title">1. Đôi Uyên Ương Ngày Chung Đôi</div>
                <div class="mayhen_paperscan_row_2col">
                    <div class="mayhen_paperscan_field_group">
                        <label class="mayhen_paperscan_label" for="groom_name">Họ và tên Chú Rể *</label>
                        <input 
                            type="text" 
                            id="groom_name" 
                            name="groom_name" 
                            class="mayhen_paperscan_input" 
                            value="{{ old('groom_name', $parsed['groom_name'] ?? '') }}" 
                            required
                        >
                    </div>

                    <div class="mayhen_paperscan_field_group">
                        <label class="mayhen_paperscan_label" for="bride_name">Họ và tên Cô Dâu *</label>
                        <input 
                            type="text" 
                            id="bride_name" 
                            name="bride_name" 
                            class="mayhen_paperscan_input" 
                            value="{{ old('bride_name', $parsed['bride_name'] ?? '') }}" 
                            required
                        >
                    </div>
                </div>

                {{-- Phần 2: Phụ Mẫu Hai Bên --}}
                <div class="mayhen_paperscan_section_title">2. Phụ Mẫu Hai Bên Gia Đình</div>
                <div class="mayhen_paperscan_row_2col">
                    <div class="mayhen_paperscan_field_group">
                        <label class="mayhen_paperscan_label" for="groom_father">Thân phụ Nhà Trai</label>
                        <input 
                            type="text" 
                            id="groom_father" 
                            name="groom_father" 
                            class="mayhen_paperscan_input" 
                            value="{{ old('groom_father', $parsed['groom_father'] ?? '') }}"
                        >
                    </div>

                    <div class="mayhen_paperscan_field_group">
                        <label class="mayhen_paperscan_label" for="groom_mother">Thân mẫu Nhà Trai</label>
                        <input 
                            type="text" 
                            id="groom_mother" 
                            name="groom_mother" 
                            class="mayhen_paperscan_input" 
                            value="{{ old('groom_mother', $parsed['groom_mother'] ?? '') }}"
                        >
                    </div>
                </div>

                <div class="mayhen_paperscan_row_2col">
                    <div class="mayhen_paperscan_field_group">
                        <label class="mayhen_paperscan_label" for="bride_father">Thân phụ Nhà Gái</label>
                        <input 
                            type="text" 
                            id="bride_father" 
                            name="bride_father" 
                            class="mayhen_paperscan_input" 
                            value="{{ old('bride_father', $parsed['bride_father'] ?? '') }}"
                        >
                    </div>

                    <div class="mayhen_paperscan_field_group">
                        <label class="mayhen_paperscan_label" for="bride_mother">Thân mẫu Nhà Gái</label>
                        <input 
                            type="text" 
                            id="bride_mother" 
                            name="bride_mother" 
                            class="mayhen_paperscan_input" 
                            value="{{ old('bride_mother', $parsed['bride_mother'] ?? '') }}"
                        >
                    </div>
                </div>

                {{-- Phần 3: Thời Gian Tổ Chức --}}
                <div class="mayhen_paperscan_section_title">3. Thời Gian & Lịch Trình Hôn Lễ</div>
                <div class="mayhen_paperscan_row_2col">
                    <div class="mayhen_paperscan_field_group">
                        <label class="mayhen_paperscan_label" for="wedding_date">Ngày cưới (Dương lịch)</label>
                        <input 
                            type="date" 
                            id="wedding_date" 
                            name="wedding_date" 
                            class="mayhen_paperscan_input" 
                            value="{{ old('wedding_date', $parsed['wedding_date'] ?? '') }}"
                        >
                    </div>

                    <div class="mayhen_paperscan_field_group">
                        <label class="mayhen_paperscan_label" for="party_time">Giờ đón khách / Khai tiệc</label>
                        <input 
                            type="text" 
                            id="party_time" 
                            name="party_time" 
                            class="mayhen_paperscan_input" 
                            value="{{ old('party_time', $parsed['party_time'] ?? '') }}"
                            placeholder="Ví dụ: 11:30 hoặc 18:00"
                        >
                    </div>
                </div>

                <div class="mayhen_paperscan_field_group">
                    <label class="mayhen_paperscan_label" for="lunar_date">Ngày Âm lịch (nếu có)</label>
                    <input 
                        type="text" 
                        id="lunar_date" 
                        name="lunar_date" 
                        class="mayhen_paperscan_input" 
                        value="{{ old('lunar_date', $parsed['lunar_date'] ?? '') }}"
                        placeholder="Ví dụ: Nhằm ngày 20 tháng 11 năm Bính Ngọ"
                    >
                </div>

                {{-- Phần 4: Địa Điểm Tổ Chức --}}
                <div class="mayhen_paperscan_section_title">4. Sảnh Tiệc & Thánh Đường</div>
                <div class="mayhen_paperscan_field_group">
                    <label class="mayhen_paperscan_label" for="venue_name">Tên Nhà Hàng / Trung Tâm Tiệc Cưới</label>
                    <input 
                        type="text" 
                        id="venue_name" 
                        name="venue_name" 
                        class="mayhen_paperscan_input" 
                        value="{{ old('venue_name', $parsed['venue_name'] ?? '') }}"
                        placeholder="Ví dụ: White Palace / Gem Center"
                    >
                </div>

                <div class="mayhen_paperscan_field_group">
                    <label class="mayhen_paperscan_label" for="venue_address">Địa chỉ chi tiết (mở chỉ đường Google Maps)</label>
                    <input 
                        type="text" 
                        id="venue_address" 
                        name="venue_address" 
                        class="mayhen_paperscan_input" 
                        value="{{ old('venue_address', $parsed['venue_address'] ?? '') }}"
                        placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành"
                    >
                </div>

                <div class="mayhen_paperscan_field_group">
                    <label class="mayhen_paperscan_label" for="quote">Lời chúc / Thông điệp tình yêu</label>
                    <textarea 
                        id="quote" 
                        name="quote" 
                        class="mayhen_paperscan_textarea" 
                        rows="2"
                    >{{ old('quote', $parsed['quote'] ?? '') }}</textarea>
                </div>

                <div style="margin-top: 28px; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <a href="{{ route('account.paper_scan.create', $invitation->id) }}" class="mayhen_account_btn mayhen_account_btn_outline">
                        &larr; Tải Lại Ảnh Khác
                    </a>
                    <button type="submit" class="mayhen_account_btn mayhen_account_btn_gold" style="padding: 12px 28px; font-weight: 600;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Xác Nhận & Áp Dụng Vào Thiệp Cưới Online</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/paper_scan.js') }}?v={{ time() }}"></script>
@endpush

