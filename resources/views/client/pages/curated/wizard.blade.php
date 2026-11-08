@extends('client.layouts.master')

@section('title', 'Tạo Thiệp Tự Động Thông Minh | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="/assets/client/css/pages/curated-wizard.css?v={{ time() }}">
@endpush

@section('content')
<div class="mayhen_curated_wizard_page">
    <div class="mayhen_curated_wizard_container">
        
        {{-- Header Tiêu Đề --}}
        <div class="mayhen_curated_wizard_header">
            <span class="mayhen_curated_kicker">AI CURATED INVITATION</span>
            <h1 class="mayhen_curated_title">Tạo Thiệp Cưới Độc Bản Dành Riêng Cho Bạn</h1>
            <p class="mayhen_curated_subtitle">
                AI chấp bút lời ngỏ &bull; Mây Hẹn tự phối bố cục nghệ thuật từ các component được kiểm duyệt khắt khe
            </p>
        </div>

        @if($errors->any())
            <div class="mayhen_curated_alert_error">
                <strong>Vui lòng kiểm tra lại thông tin:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Nhập Liệu --}}
        <form action="{{ route('curated.generate') }}" method="POST" id="curatedWizardForm" class="mayhen_curated_form">
            @csrf

            {{-- BƯỚC 1: HAI BẠN LÀ AI? --}}
            <div class="mayhen_curated_step_card">
                <div class="mayhen_curated_step_num">1</div>
                <div class="mayhen_curated_step_content">
                    <h2 class="mayhen_curated_step_heading">Hai Bạn Là Ai?</h2>
                    <p class="mayhen_curated_step_desc">Nhập họ tên cô dâu và chú rể để AI biên soạn lời ngỏ và dàn trang thiệp cưới riêng cho hai bạn.</p>
                    
                    <div class="mayhen_curated_inputs_grid">
                        <div class="mayhen_curated_field">
                            <label for="groom_name">Họ Tên Chú Rể <span class="req">*</span></label>
                            <input type="text" name="groom_name" id="groom_name" value="{{ old('groom_name', 'Minh Tuấn') }}" placeholder="Ví dụ: Minh Tuấn" required maxlength="50" autocomplete="off">
                        </div>
                        <div class="mayhen_curated_field">
                            <label for="bride_name">Họ Tên Cô Dâu <span class="req">*</span></label>
                            <input type="text" name="bride_name" id="bride_name" value="{{ old('bride_name', 'Thu Hà') }}" placeholder="Ví dụ: Thu Hà" required maxlength="50" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>

            {{-- BƯỚC 2: HAI BÊN GIA ĐÌNH --}}
            <div class="mayhen_curated_step_card">
                <div class="mayhen_curated_step_num">2</div>
                <div class="mayhen_curated_step_content">
                    <h2 class="mayhen_curated_step_heading">Hai Bên Gia Đình (Tùy chọn)</h2>
                    <p class="mayhen_curated_step_desc">Thông tin song thân phụ mẫu giúp danh thiếp trọn vẹn hiếu nghĩa. Bạn có thể bổ sung hoặc cập nhật lại bất cứ lúc nào trong Studio.</p>
                    
                    <div class="mayhen_curated_inputs_grid_4">
                        <div class="mayhen_curated_field">
                            <label for="groom_father">Thân phụ Chú rể</label>
                            <input type="text" name="groom_father" id="groom_father" value="{{ old('groom_father', 'Nguyễn Văn Hùng') }}" placeholder="Ông Nguyễn Văn Hùng" maxlength="50">
                        </div>
                        <div class="mayhen_curated_field">
                            <label for="groom_mother">Thân mẫu Chú rể</label>
                            <input type="text" name="groom_mother" id="groom_mother" value="{{ old('groom_mother', 'Lê Thị Thu') }}" placeholder="Bà Lê Thị Thu" maxlength="50">
                        </div>
                        <div class="mayhen_curated_field">
                            <label for="bride_father">Thân phụ Cô dâu</label>
                            <input type="text" name="bride_father" id="bride_father" value="{{ old('bride_father', 'Trần Quốc Bảo') }}" placeholder="Ông Trần Quốc Bảo" maxlength="50">
                        </div>
                        <div class="mayhen_curated_field">
                            <label for="bride_mother">Thân mẫu Cô dâu</label>
                            <input type="text" name="bride_mother" id="bride_mother" value="{{ old('bride_mother', 'Phạm Thị Mai') }}" placeholder="Bà Phạm Thị Mai" maxlength="50">
                        </div>
                    </div>
                </div>
            </div>

            {{-- BƯỚC 3: PHONG CÁCH CỦA BẠN --}}
            <div class="mayhen_curated_step_card">
                <div class="mayhen_curated_step_num">3</div>
                <div class="mayhen_curated_step_content">
                    <h2 class="mayhen_curated_step_heading">Phong Cách Thẩm Mỹ (Style)</h2>
                    <p class="mayhen_curated_step_desc">Lựa chọn 1 trong 4 phong cách định sẵn của Mây Hẹn để hệ thống định hình tinh thần và câu chữ thiệp cưới.</p>
                    
                    <div class="mayhen_curated_styles_grid">
                        @foreach($styles as $sKey => $sVal)
                            <label class="mayhen_curated_style_label">
                                <input type="radio" name="style" value="{{ $sKey }}" {{ old('style', 'romantic') === $sKey ? 'checked' : '' }}>
                                <div class="mayhen_curated_style_box">
                                    <span class="mayhen_curated_style_badge">{{ $sVal['badge'] }}</span>
                                    <h3 class="mayhen_curated_style_name">{{ $sVal['name'] }}</h3>
                                    <span class="mayhen_curated_style_en">{{ $sVal['english_name'] }}</span>
                                    <p class="mayhen_curated_style_desc">{{ $sVal['description'] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- BƯỚC 4: BẢNG MÀU & THỜI KHẮC TRỌNG ĐẠI --}}
            <div class="mayhen_curated_step_card">
                <div class="mayhen_curated_step_num">4</div>
                <div class="mayhen_curated_step_content">
                    <h2 class="mayhen_curated_step_heading">Bảng Màu &amp; Thời Khắc Trọng Đại</h2>
                    <p class="mayhen_curated_step_desc">Màu sắc do bạn chọn và được cố định bất biến khi phối lại bố cục. Ngày cưới và sảnh tiệc có thể tùy chỉnh lại trong Studio.</p>
                    
                    <div class="mayhen_curated_palettes_grid">
                        @foreach($palettes as $pKey => $pVal)
                            <label class="mayhen_curated_palette_label">
                                <input type="radio" name="palette" value="{{ $pKey }}" {{ old('palette', 'ruby') === $pKey ? 'checked' : '' }}>
                                <div class="mayhen_curated_palette_box">
                                    <div class="mayhen_curated_palette_swatches">
                                        <span style="background-color: {{ $pVal['primary'] }};"></span>
                                        <span style="background-color: {{ $pVal['accent'] }};"></span>
                                        <span style="background-color: {{ $pVal['background'] }}; border: 1px solid #ddd;"></span>
                                    </div>
                                    <div class="mayhen_curated_palette_info">
                                        <strong class="mayhen_curated_palette_name">{{ $pVal['name'] }}</strong>
                                        <span class="mayhen_curated_palette_tag">{{ $pVal['tag'] }}</span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="mayhen_curated_inputs_grid_3" style="margin-top: 24px;">
                        <div class="mayhen_curated_field">
                            <label for="wedding_date">Ngày Cưới Dương Lịch</label>
                            <input type="date" name="wedding_date" id="wedding_date" value="{{ old('wedding_date', now()->addMonths(2)->format('Y-m-d')) }}">
                        </div>
                        <div class="mayhen_curated_field">
                            <label for="venue_name">Tên Trung Tâm / Sảnh Tiệc</label>
                            <input type="text" name="venue_name" id="venue_name" value="{{ old('venue_name', 'Trung Tâm Tiệc Cưới Ngân Hà') }}" placeholder="Ví dụ: GEM Center" maxlength="100">
                        </div>
                        <div class="mayhen_curated_field">
                            <label for="venue_address">Địa Chỉ Sảnh Tiệc</label>
                            <input type="text" name="venue_address" id="venue_address" value="{{ old('venue_address', 'Số 88 Trần Duy Hưng, Cầu Giấy, Hà Nội') }}" placeholder="Số nhà, đường, quận, thành phố" maxlength="255">
                        </div>
                    </div>
                </div>
            </div>

            {{-- KHỐI TÓM TẮT THIẾT KẾ (DESIGN SUMMARY) --}}
            <div class="mayhen_curated_summary_card" id="curatedSummaryCard">
                <div class="summary_crest">✦ MÂY HẸN &bull; QUIET LUXURY ATELIER ✦</div>
                <h3 class="summary_title">Tóm Tắt Bản Thiết Kế Dành Riêng Cho Hai Bạn</h3>
                <div class="summary_grid">
                    <div class="summary_item">
                        <span class="summary_label">Đôi Uyên Ương:</span>
                        <strong class="summary_val" id="summaryCoupleText">Minh Tuấn &amp; Thu Hà</strong>
                    </div>
                    <div class="summary_item">
                        <span class="summary_label">Phong Cách:</span>
                        <strong class="summary_val" id="summaryStyleText">Lãng Mạn (Romantic Poetry)</strong>
                    </div>
                    <div class="summary_item">
                        <span class="summary_label">Tông Màu Cố Định:</span>
                        <strong class="summary_val" id="summaryPaletteText">Đỏ Rượu Vang &amp; Vàng Antique</strong>
                    </div>
                    <div class="summary_item">
                        <span class="summary_label">Cấu Trúc Mỹ Thuật:</span>
                        <strong class="summary_val">7 Section Chuẩn Mực (Có Album 10 Ảnh)</strong>
                    </div>
                </div>
                <p class="summary_note">
                    📸 <strong>Lưu ý ảnh cưới:</strong> Thiệp khởi tạo sẽ sử dụng khung chờ ảnh mỹ thuật trung tính để bạn chiêm ngưỡng tổng thể dàn trang; bạn có thể tải ảnh cưới thật của mình lên bất cứ lúc nào trong Studio.
                </p>
            </div>

            {{-- Nút Bấm Khởi Tạo --}}
            <div class="mayhen_curated_submit_bar">
                <button type="submit" class="mayhen_curated_btn_generate" id="submitCuratedBtn">
                    <span class="btn_text">✦ Tạo Thiệp Của Chúng Mình ✦</span>
                    <span class="btn_sub">AI biên soạn lời ngỏ &bull; Mây Hẹn tự phối 7 section nghệ thuật</span>
                </button>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
    <script src="/assets/client/js/pages/curated-wizard.js?v={{ time() }}"></script>
@endpush

