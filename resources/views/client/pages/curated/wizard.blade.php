@extends('client.layouts.master')

@php
    $wizardBrandName = \App\Models\PlatformSetting::getBrandName();
    $wizardBrandNameUpper = \App\Models\PlatformSetting::getBrandNameUpper();
    $defaultDate = old('wedding_date', now()->addMonths(2)->format('Y-m-d'));
    $selectedStyle = old('style', 'romantic');
    $selectedPalette = old('palette', 'ruby');
    $selectedCeremony = old('ceremony_type', 'Lễ Thành Hôn');
    $selectedPartyTime = old('party_time', '18:00');
    $selectedLayoutMode = old('layout_mode', 'curated_7');
@endphp

@section('title', 'Tạo Thiệp Tự Động Thông Minh | ' . $wizardBrandName)

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/curated-wizard.css') }}">
@endpush

@section('content')
<div class="mayhen_curated_wizard_page">
    <div class="mayhen_curated_wizard_container">

        {{-- Header Tiêu Đề --}}
        <header class="mayhen_curated_wizard_header">
            <span class="mayhen_curated_kicker">✦ AI CURATED INVITATION</span>
            <h1 class="mayhen_curated_title">Tạo Thiệp Cưới Độc Bản Dành Riêng Cho Bạn</h1>
            <p class="mayhen_curated_subtitle">
                AI chấp bút lời ngỏ &bull; {{ $wizardBrandName }} tự phối bố cục nghệ thuật chuẩn Quiet Luxury chỉ trong 30 giây
            </p>

            {{-- Thanh bước nhanh (Quick Step Pills) --}}
            <nav class="mayhen_curated_step_pills" aria-label="Các bước tạo thiệp">
                <a href="#step-couple" class="step_pill is-active"><span class="step_pill_dot">1</span> Đôi uyên ương</a>
                <a href="#step-style" class="step_pill"><span class="step_pill_dot">2</span> Phong cách</a>
                <a href="#step-palette" class="step_pill"><span class="step_pill_dot">3</span> Bảng màu</a>
                <a href="#step-event" class="step_pill"><span class="step_pill_dot">4</span> Thời gian &amp; Sảnh</a>
            </nav>
        </header>

        @if(!($hasPlusAccess ?? true))
            <div class="mayhen_curated_upgrade_gate" role="region" aria-label="Yêu cầu nâng cấp Gói Plus">
                <div class="mayhen_curated_upgrade_gate_copy">
                    <span class="mayhen_curated_upgrade_gate_badge">✦ Quyền Lợi Gói Plus &amp; Pro</span>
                    <h2 class="mayhen_curated_upgrade_gate_title">Nâng cấp Gói Plus để mở khóa Tạo Thiệp Tự Động AI</h2>
                    <p class="mayhen_curated_upgrade_gate_desc">
                        Tài khoản hiện tại của bạn đang ở hạng <strong>{{ $currentPlanLabel ?? 'Thành viên' }}</strong>. Chức năng AI Curated tự phối bố cục, đổi mẫu cùng gu và viết lời ngỏ thông minh dành riêng cho khách hàng <strong>Gói Plus (99.000đ)</strong> và <strong>Gói Pro VIP (199.000đ)</strong>.
                    </p>
                </div>
                <div class="mayhen_curated_upgrade_gate_actions">
                    <a href="{{ route('checkout.show', 'plus') }}" class="mayhen_curated_upgrade_btn_primary">Nâng Cấp Gói Plus (99k) →</a>
                    <a href="{{ route('pricing') }}" class="mayhen_curated_upgrade_btn_secondary">Xem Bảng Giá</a>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mayhen_curated_alert_error" role="alert">
                <strong>Vui lòng kiểm tra lại thông tin:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div id="wizardInlineError" class="mayhen_curated_alert_error" style="display: none;" role="alert"></div>

        {{-- Form Nhập Liệu 2 Cột (Trái: Form 4 Bước, Phải: Live Mini-Card Preview & Tóm tắt Sticky) --}}
        <form action="{{ route('curated.generate') }}" method="POST" id="curatedWizardForm" class="mayhen_curated_form_layout">
            @csrf

            <div class="mayhen_curated_form_main">

                {{-- BƯỚC 1: HAI BẠN LÀ AI & LOẠI HÔN LỄ --}}
                <section class="mayhen_curated_step_card" id="step-couple">
                    <div class="mayhen_curated_step_head">
                        <span class="mayhen_curated_step_num">1</span>
                        <div>
                            <h2 class="mayhen_curated_step_heading">Hai Bạn Là Ai?</h2>
                            <p class="mayhen_curated_step_desc">Nhập tên uyên ương và loại nghi lễ để AI cá nhân hóa lời ngỏ riêng cho hai bạn.</p>
                        </div>
                    </div>

                    <div class="mayhen_curated_inputs_grid">
                        <div class="mayhen_curated_field">
                            <label for="groom_name">Tên Chú Rể <span class="req">*</span></label>
                            <input type="text" name="groom_name" id="groom_name" value="{{ old('groom_name', 'Minh Tuấn') }}" placeholder="Ví dụ: Minh Tuấn" required maxlength="50" autocomplete="off">
                        </div>
                        <div class="mayhen_curated_field">
                            <label for="bride_name">Tên Cô Dâu <span class="req">*</span></label>
                            <input type="text" name="bride_name" id="bride_name" value="{{ old('bride_name', 'Thu Hà') }}" placeholder="Ví dụ: Thu Hà" required maxlength="50" autocomplete="off">
                        </div>
                    </div>

                    {{-- Chọn nhanh loại hình hôn lễ --}}
                    <div class="mayhen_curated_subfield">
                        <span class="mayhen_curated_sublabel">Loại hình hôn lễ</span>
                        <div class="mayhen_curated_chip_group" role="radiogroup" aria-label="Loại hình hôn lễ">
                            @foreach(['Lễ Thành Hôn', 'Lễ Vu Quy', 'Lễ Tân Hôn', 'Tiệc Báo Hỷ'] as $cType)
                                <label class="mayhen_curated_chip">
                                    <input type="radio" name="ceremony_type" value="{{ $cType }}" {{ $selectedCeremony === $cType ? 'checked' : '' }}>
                                    <span>{{ $cType }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Song thân hai họ (Thu gọn mặc định cho gọn sạch trên điện thoại) --}}
                    <details class="mayhen_curated_accordion" id="parentsAccordion">
                        <summary class="mayhen_curated_accordion_trigger">
                            <span>
                                <strong>Hai Bên Gia Đình (Song thân)</strong>
                                <small>Tùy chọn &bull; Có sẵn thông tin mẫu, bạn có thể sửa ngay hoặc để sau</small>
                            </span>
                            <svg class="accordion_chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </summary>
                        <div class="mayhen_curated_accordion_body">
                            <div class="mayhen_curated_inputs_grid_4">
                                <div class="mayhen_curated_field">
                                    <label for="groom_father">Thân phụ Chú rể</label>
                                    <input type="text" name="groom_father" id="groom_father" value="{{ old('groom_father', 'Ông Nguyễn Văn Hùng') }}" placeholder="Ông Nguyễn Văn Hùng" maxlength="50">
                                </div>
                                <div class="mayhen_curated_field">
                                    <label for="groom_mother">Thân mẫu Chú rể</label>
                                    <input type="text" name="groom_mother" id="groom_mother" value="{{ old('groom_mother', 'Bà Lê Thị Thu') }}" placeholder="Bà Lê Thị Thu" maxlength="50">
                                </div>
                                <div class="mayhen_curated_field">
                                    <label for="bride_father">Thân phụ Cô dâu</label>
                                    <input type="text" name="bride_father" id="bride_father" value="{{ old('bride_father', 'Ông Trần Quốc Bảo') }}" placeholder="Ông Trần Quốc Bảo" maxlength="50">
                                </div>
                                <div class="mayhen_curated_field">
                                    <label for="bride_mother">Thân mẫu Cô dâu</label>
                                    <input type="text" name="bride_mother" id="bride_mother" value="{{ old('bride_mother', 'Bà Phạm Thị Mai') }}" placeholder="Bà Phạm Thị Mai" maxlength="50">
                                </div>
                            </div>
                        </div>
                    </details>
                </section>

                {{-- BƯỚC 2: PHONG CÁCH THẨM MỸ --}}
                <section class="mayhen_curated_step_card" id="step-style">
                    <div class="mayhen_curated_step_head">
                        <span class="mayhen_curated_step_num">2</span>
                        <div>
                            <h2 class="mayhen_curated_step_heading">Phong Cách Thẩm Mỹ (Style)</h2>
                            <p class="mayhen_curated_step_desc">Chọn tinh thần chủ đạo để hệ thống chọn mẫu thiệp gốc phù hợp và định hướng giọng văn AI.</p>
                        </div>
                    </div>

                    <div class="mayhen_curated_styles_grid">
                        @foreach($styles as $sKey => $sVal)
                            <label class="mayhen_curated_style_label">
                                <input type="radio" name="style" value="{{ $sKey }}" {{ $selectedStyle === $sKey ? 'checked' : '' }} data-recommended-palette="{{ $sVal['recommended_palette'] ?? 'ruby' }}">
                                <div class="mayhen_curated_style_box">
                                    <div class="style_box_top">
                                        <span class="mayhen_curated_style_badge">{{ $sVal['badge'] }}</span>
                                        <span class="style_check_mark" aria-hidden="true">✓</span>
                                    </div>
                                    <h3 class="mayhen_curated_style_name">{{ $sVal['name'] }}</h3>
                                    <span class="mayhen_curated_style_en">{{ $sVal['english_name'] }}</span>
                                    <p class="mayhen_curated_style_desc">{{ $sVal['description'] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </section>

                {{-- BƯỚC 3: BẢNG MÀU & CẤU TRÚC THIỆP --}}
                <section class="mayhen_curated_step_card" id="step-palette">
                    <div class="mayhen_curated_step_head">
                        <span class="mayhen_curated_step_num">3</span>
                        <div>
                            <h2 class="mayhen_curated_step_heading">Bảng Màu &amp; Cấu Trúc Thiệp</h2>
                            <p class="mayhen_curated_step_desc">Bảng màu được đồng bộ xuyên suốt toàn thiệp và có thể đổi lại bất kỳ lúc nào ở màn hình xem trước.</p>
                        </div>
                    </div>

                    <div class="mayhen_curated_palettes_grid">
                        @foreach($palettes as $pKey => $pVal)
                            <label class="mayhen_curated_palette_label">
                                <input type="radio" name="palette" value="{{ $pKey }}" {{ $selectedPalette === $pKey ? 'checked' : '' }}
                                       data-primary="{{ $pVal['primary'] }}"
                                       data-accent="{{ $pVal['accent'] }}"
                                       data-bg="{{ $pVal['background'] }}"
                                       data-name="{{ $pVal['name'] }}">
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

                    {{-- Chọn chế độ 7 mục tinh gọn hay Đầy đủ 10 mục --}}
                    <div class="mayhen_curated_subfield">
                        <span class="mayhen_curated_sublabel">Quy mô nội dung thiệp</span>
                        <div class="mayhen_curated_mode_grid">
                            <label class="mayhen_curated_mode_card">
                                <input type="radio" name="layout_mode" value="curated_7" {{ $selectedLayoutMode !== 'full_10' ? 'checked' : '' }}>
                                <div class="mode_card_inner">
                                    <strong>✦ 7 Mục Tinh Gọn (Khuyên dùng)</strong>
                                    <span>Trang bìa, Chuyện tình, Song thân, Đếm ngược, Sự kiện &amp; Bản đồ, Lịch trình, Album 10 ảnh.</span>
                                </div>
                            </label>
                            <label class="mayhen_curated_mode_card">
                                <input type="radio" name="layout_mode" value="full_10" {{ $selectedLayoutMode === 'full_10' ? 'checked' : '' }}>
                                <div class="mode_card_inner">
                                    <strong>✦ Đầy Đủ 10 Mục Tương Tác</strong>
                                    <span>Mở khóa thêm Quy định trang phục, Hộp mừng cưới VietQR, Ký tên, Xác nhận RSVP &amp; Sổ lời chúc.</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </section>

                {{-- BƯỚC 4: THỜI KHẮC TRỌNG ĐẠI & SẢNH TIỆC --}}
                <section class="mayhen_curated_step_card" id="step-event">
                    <div class="mayhen_curated_step_head">
                        <span class="mayhen_curated_step_num">4</span>
                        <div>
                            <h2 class="mayhen_curated_step_heading">Thời Khắc Trọng Đại &amp; Sảnh Tiệc</h2>
                            <p class="mayhen_curated_step_desc">Thông tin ngày giờ và địa điểm giúp tự động tạo lịch tháng, đồng hồ đếm ngược và bản đồ Google Maps.</p>
                        </div>
                    </div>

                    <div class="mayhen_curated_inputs_grid">
                        <div class="mayhen_curated_field">
                            <label for="wedding_date">Ngày Cưới Dương Lịch</label>
                            <input type="date" name="wedding_date" id="wedding_date" value="{{ $defaultDate }}">
                        </div>
                        <div class="mayhen_curated_field">
                            <label for="party_time">Giờ Khai Tiệc</label>
                            <div class="time_input_row">
                                <input type="time" name="party_time" id="party_time" value="{{ $selectedPartyTime }}">
                                <div class="time_quick_btns">
                                    <button type="button" class="time_quick_btn" data-set-time="11:00">11:00 Trưa</button>
                                    <button type="button" class="time_quick_btn" data-set-time="18:00">18:00 Tối</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mayhen_curated_inputs_grid" style="margin-top: 14px;">
                        <div class="mayhen_curated_field">
                            <label for="venue_name">Tên Trung Tâm / Sảnh Tiệc</label>
                            <input type="text" name="venue_name" id="venue_name" value="{{ old('venue_name', 'Trung Tâm Tiệc Cưới Ngân Hà') }}" placeholder="Ví dụ: GEM Center - Sảnh Sol" maxlength="100">
                        </div>
                        <div class="mayhen_curated_field">
                            <label for="venue_address">Địa Chỉ Sảnh Tiệc</label>
                            <input type="text" name="venue_address" id="venue_address" value="{{ old('venue_address', 'Số 88 Trần Duy Hưng, Cầu Giấy, Hà Nội') }}" placeholder="Số nhà, tên đường, quận, thành phố" maxlength="255">
                        </div>
                    </div>
                </section>

            </div>

            {{-- CỘT PHẢI: LIVE MINI-CARD PREVIEW + TÓM TẮT + NÚT KHỞI TẠO --}}
            <aside class="mayhen_curated_form_aside">
                <div class="mayhen_curated_sticky_panel">

                    {{-- MÔ PHỎNG BÌA THIỆP TRỰC QUAN REALTIME --}}
                    <div class="mayhen_curated_mini_card" id="wizardMiniCard">
                        <div class="mini_card_frame">
                            <span class="mini_card_ceremony" id="miniCardCeremony">{{ $selectedCeremony }}</span>
                            <div class="mini_card_names">
                                <span id="miniCardGroom">{{ old('groom_name', 'Minh Tuấn') }}</span>
                                <em class="mini_card_amp">&amp;</em>
                                <span id="miniCardBride">{{ old('bride_name', 'Thu Hà') }}</span>
                            </div>
                            <div class="mini_card_divider"><span></span><i>✦</i><span></span></div>
                            <div class="mini_card_date" id="miniCardDate">{{ \Illuminate\Support\Carbon::parse($defaultDate)->format('d · m · Y') }}</div>
                            <div class="mini_card_venue" id="miniCardVenue">{{ old('venue_name', 'Trung Tâm Tiệc Cưới Ngân Hà') }}</div>
                        </div>
                    </div>

                    {{-- KHỐI TÓM TẮT THIẾT KẾ (DESIGN SUMMARY) --}}
                    <div class="mayhen_curated_summary_card" id="curatedSummaryCard">
                        <div class="summary_crest">✦ {{ $wizardBrandNameUpper }} &bull; QUIET LUXURY ✦</div>
                        <h3 class="summary_title">Tóm Tắt Bản Thiết Kế Dành Riêng Cho Hai Bạn</h3>
                        <div class="summary_grid">
                            <div class="summary_item">
                                <span class="summary_label">Đôi Uyên Ương</span>
                                <strong class="summary_val" id="summaryCoupleText">Minh Tuấn &amp; Thu Hà</strong>
                            </div>
                            <div class="summary_item">
                                <span class="summary_label">Phong Cách</span>
                                <strong class="summary_val" id="summaryStyleText">Lãng Mạn &amp; Bay Bổng</strong>
                            </div>
                            <div class="summary_item">
                                <span class="summary_label">Tông Màu Chủ Đạo</span>
                                <strong class="summary_val" id="summaryPaletteText">Đỏ Rượu Vang &amp; Vàng Antique</strong>
                            </div>
                            <div class="summary_item">
                                <span class="summary_label">Cấu Trúc Mỹ Thuật</span>
                                <strong class="summary_val" id="summaryLayoutText">7 Section Tinh Gọn</strong>
                            </div>
                        </div>
                        <p class="summary_note">
                            Sau khi khởi tạo, bạn có thể đổi mẫu thiệp, đổi màu, nhờ AI viết lại lời ngỏ hoặc chỉnh trực tiếp từng chi tiết ngay lập tức.
                        </p>

                        <div class="mayhen_curated_submit_bar">
                            <button type="submit" class="mayhen_curated_btn_generate" id="submitCuratedBtn">
                                <span class="btn_text">✦ Tạo Thiệp Của Chúng Mình ✦</span>
                                <span class="btn_sub">AI biên soạn lời ngỏ &bull; {{ $wizardBrandName }} tự phối bố cục</span>
                            </button>
                        </div>
                    </div>

                </div>
            </aside>

        </form>

    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset_v('assets/client/js/pages/curated-wizard.js') }}"></script>
@endpush
