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

            {{-- BƯỚC 1: ĐÔI UYÊN ƯƠNG & PHỤ MẪU --}}
            <div class="mayhen_curated_step_card">
                <div class="mayhen_curated_step_num">1</div>
                <div class="mayhen_curated_step_content">
                    <h2 class="mayhen_curated_step_heading">Thông Tin Đôi Uyên Ương &amp; Hai Họ</h2>
                    <p class="mayhen_curated_step_desc">Nhập tên cô dâu chú rể và song thân phụ mẫu để AI chấp bút lời chúc và thiết kế danh thiếp.</p>
                    
                    <div class="mayhen_curated_inputs_grid">
                        <div class="mayhen_curated_field">
                            <label for="groom_name">Họ Tên Chú Rể <span class="req">*</span></label>
                            <input type="text" name="groom_name" id="groom_name" value="{{ old('groom_name', 'Minh Tuấn') }}" placeholder="Ví dụ: Minh Tuấn" required maxlength="50">
                        </div>
                        <div class="mayhen_curated_field">
                            <label for="bride_name">Họ Tên Cô Dâu <span class="req">*</span></label>
                            <input type="text" name="bride_name" id="bride_name" value="{{ old('bride_name', 'Thu Hà') }}" placeholder="Ví dụ: Thu Hà" required maxlength="50">
                        </div>
                    </div>

                    <div class="mayhen_curated_parents_toggle_wrap">
                        <h3 class="mayhen_curated_subheading">Phụ Mẫu Hai Bên (Tùy chọn)</h3>
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
            </div>

            {{-- BƯỚC 2: CHỌN PHONG CÁCH ĐỊNH SẴN (MỤC 2) --}}
            <div class="mayhen_curated_step_card">
                <div class="mayhen_curated_step_num">2</div>
                <div class="mayhen_curated_step_content">
                    <h2 class="mayhen_curated_step_heading">Chọn Phong Cách Thẩm Mỹ (Style)</h2>
                    <p class="mayhen_curated_step_desc">Lựa chọn 1 trong 4 phong cách định sẵn của Mây Hẹn để hệ thống định hình tinh thần thiệp cưới.</p>
                    
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

            {{-- BƯỚC 3: CHỌN BẢNG MÀU YÊU THÍCH (MỤC 3) --}}
            <div class="mayhen_curated_step_card">
                <div class="mayhen_curated_step_num">3</div>
                <div class="mayhen_curated_step_content">
                    <h2 class="mayhen_curated_step_heading">Chọn Bảng Màu Chủ Đạo (Palette)</h2>
                    <p class="mayhen_curated_step_desc">Màu sắc do bạn quyết định. Khi phối lại bố cục hay tạo nội dung mới, hệ thống cam kết giữ nguyên màu sắc đã chọn.</p>
                    
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
                </div>
            </div>

            {{-- BƯỚC 4: THỜI GIAN & ĐỊA ĐIỂM (TÙY CHỌN) --}}
            <div class="mayhen_curated_step_card">
                <div class="mayhen_curated_step_num">4</div>
                <div class="mayhen_curated_step_content">
                    <h2 class="mayhen_curated_step_heading">Thời Gian &amp; Địa Điểm Cưới</h2>
                    <p class="mayhen_curated_step_desc">Bạn có thể nhập ngay hoặc bổ sung sau trong Studio.</p>
                    
                    <div class="mayhen_curated_inputs_grid_3">
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

            {{-- Nút Bấm Khởi Tạo --}}
            <div class="mayhen_curated_submit_bar">
                <button type="submit" class="mayhen_curated_btn_generate" id="submitCuratedBtn">
                    <span class="btn_text">✦ Bắt Đầu Dựng Thiệp Tự Động ✦</span>
                    <span class="btn_sub">AI viết nội dung &bull; Tự phối 7 section nghệ thuật</span>
                </button>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
    <script src="/assets/client/js/pages/curated-wizard.js?v={{ time() }}"></script>
@endpush

