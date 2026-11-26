@extends('client.layouts.master')

@section('title', $pageMeta['title'])
@section('meta_description', $pageMeta['meta_description'])
@section('canonical', $pageMeta['canonical'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/marriage.css') }}?v={{ filemtime(public_path('assets/client/css/pages/marriage.css')) }}">
@endpush

@section('content')
<div class="mayhen_marriage_page">
    <section class="mayhen_marriage_hero">
        <div class="mayhen_marriage_container">
            <div class="mayhen_marriage_eyebrow">✦ MÂY HẸN – DUYÊN HỢP · ĐỐI CHIẾU ĐA PHƯƠNG PHÁP KHÔNG CHẤM % GIẢ</div>
            <h1 class="mayhen_marriage_h1">{{ $pageMeta['h1'] }}</h1>
            <p class="mayhen_marriage_lead">
                Khám phá đồng thời 6 lớp tri thức hợp hôn cổ truyền: <strong>Can Chi</strong>, <strong>Ngũ Hành Nạp Âm</strong>, <strong>Cung Phi &amp; Bát Trạch Du Niên 8×8</strong>, <strong>Cao Ly Đầu Hình</strong> và <strong>Kim Lâu năm cưới</strong> — trình bày minh bạch, nhân văn và không chấm điểm % hợp nhau giả.
            </p>

            <nav class="mayhen_marriage_nav" aria-label="Điều hướng Xem Tuổi Vợ Chồng">
                <a href="{{ route('marriage.compatibility.index') }}" class="mayhen_marriage_pill is-active">Xem Tuổi Vợ Chồng</a>
                <a href="{{ route('marriage.caoly.index') }}" class="mayhen_marriage_pill">Vòng Tra Cao Ly Đầu Hình</a>
                <a href="{{ route('marriage.caoly.matrix') }}" class="mayhen_marriage_pill">Bảng Tra 120 Tổ Hợp</a>
                <a href="{{ route('bazi.love') }}" class="mayhen_marriage_pill">Bát Tự Tình Duyên</a>
                <a href="{{ route('calendar.index') }}" class="mayhen_marriage_pill">Xem Ngày Cưới (Lịch Âm)</a>
                @auth
                    <a href="{{ route('marriage.book.index') }}" class="mayhen_marriage_pill">Sổ Nhân Duyên Của Tôi</a>
                @endauth
            </nav>

            <div class="mayhen_marriage_two_col">
                {{-- Form Nhập Thông Tin Hai Người --}}
                <div class="mayhen_marriage_card">
                    <h2 class="mayhen_marriage_card_title">Hai Tuổi, Một Lời Hẹn</h2>
                    <p class="mayhen_marriage_card_sub">Tra cứu cơ bản miễn phí, không yêu cầu email. Nhập thêm ngày tháng sinh để hệ thống tự xử lý chính xác người sinh trước/sau Tết Nguyên Đán.</p>

                    <div class="mayhen_marriage_plan_bar">
                        @if ($marriageQuota['is_logged_in'])
                            <div>
                                <span class="mayhen_marriage_badge">Gói {{ $marriageQuota['plan_label'] }}</span>
                                <span style="margin-left:6px;color:#4E342E;">Còn <strong>{{ $marriageQuota['remaining_today'] }}/{{ $marriageQuota['daily_limit'] }}</strong> lượt lưu Sổ Nhân Duyên &amp; AI hôm nay · {{ $marriageQuota['cooldown_label'] }}</span>
                            </div>
                        @else
                            <div style="color:#4E342E;">
                                <strong>Chế độ Khách:</strong> Tra cứu đa phương pháp miễn phí. <a href="{{ route('login') }}" style="color:#85182A;font-weight:600;">Đăng nhập (Premium 0đ)</a> để lưu Sổ Nhân Duyên &amp; bật AI luận giải 10 tầng.
                            </div>
                        @endif
                    </div>

                    <form id="mayhenMarriageForm" data-endpoint="{{ route('marriage.analyze') }}">
                        <div style="padding:12px;background:#FAF5EB;border:1px solid var(--mayhen-cao-ly-border);border-radius:12px;margin-bottom:12px;">
                            <div style="font-weight:700;color:#1E4D3B;font-size:13.5px;margin-bottom:8px;">Người Chồng (Can Chồng)</div>
                            <div class="mayhen_marriage_form_row">
                                <div class="mayhen_marriage_field">
                                    <label>Tên / Biệt danh (tùy chọn)</label>
                                    <input name="husband_name" type="text" class="mayhen_marriage_input" placeholder="Ví dụ: Minh Khang">
                                </div>
                                <div class="mayhen_marriage_field">
                                    <label>Loại lịch</label>
                                    <select name="husband_calendar_type" class="mayhen_marriage_select">
                                        <option value="solar">Dương lịch</option>
                                        <option value="lunar">Âm lịch Việt Nam</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mayhen_marriage_form_row mayhen_marriage_form_row--3">
                                <div class="mayhen_marriage_field">
                                    <label>Năm sinh *</label>
                                    <input name="husband_year" type="number" min="1900" max="2100" value="1990" class="mayhen_marriage_input" required>
                                </div>
                                <div class="mayhen_marriage_field">
                                    <label>Tháng (tùy chọn)</label>
                                    <input name="husband_month" type="number" min="1" max="12" placeholder="1–12" class="mayhen_marriage_input">
                                </div>
                                <div class="mayhen_marriage_field">
                                    <label>Ngày (tùy chọn)</label>
                                    <input name="husband_day" type="number" min="1" max="31" placeholder="1–31" class="mayhen_marriage_input">
                                </div>
                            </div>
                        </div>

                        <div style="padding:12px;background:#FAF5EB;border:1px solid var(--mayhen-cao-ly-border);border-radius:12px;margin-bottom:12px;">
                            <div style="font-weight:700;color:#85182A;font-size:13.5px;margin-bottom:8px;">Người Vợ (Chi Vợ)</div>
                            <div class="mayhen_marriage_form_row">
                                <div class="mayhen_marriage_field">
                                    <label>Tên / Biệt danh (tùy chọn)</label>
                                    <input name="wife_name" type="text" class="mayhen_marriage_input" placeholder="Ví dụ: Ngọc Lan">
                                </div>
                                <div class="mayhen_marriage_field">
                                    <label>Loại lịch</label>
                                    <select name="wife_calendar_type" class="mayhen_marriage_select">
                                        <option value="solar">Dương lịch</option>
                                        <option value="lunar">Âm lịch Việt Nam</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mayhen_marriage_form_row mayhen_marriage_form_row--3">
                                <div class="mayhen_marriage_field">
                                    <label>Năm sinh *</label>
                                    <input name="wife_year" type="number" min="1900" max="2100" value="1991" class="mayhen_marriage_input" required>
                                </div>
                                <div class="mayhen_marriage_field">
                                    <label>Tháng (tùy chọn)</label>
                                    <input name="wife_month" type="number" min="1" max="12" placeholder="1–12" class="mayhen_marriage_input">
                                </div>
                                <div class="mayhen_marriage_field">
                                    <label>Ngày (tùy chọn)</label>
                                    <input name="wife_day" type="number" min="1" max="31" placeholder="1–31" class="mayhen_marriage_input">
                                </div>
                            </div>
                        </div>

                        <div class="mayhen_marriage_form_row">
                            <div class="mayhen_marriage_field">
                                <label>Năm dự kiến kết hôn</label>
                                <input name="wedding_year" type="number" min="1950" max="2100" value="{{ now('Asia/Ho_Chi_Minh')->year + 1 }}" class="mayhen_marriage_input">
                            </div>
                            <div class="mayhen_marriage_field" style="justify-content:flex-end;">
                                @auth
                                    <label style="display:inline-flex;align-items:center;gap:6px;margin-bottom:4px;">
                                        <input type="checkbox" name="save_report" value="1" checked> Lưu Sổ Nhân Duyên &amp; mở bài luận
                                    </label>
                                    <label style="display:inline-flex;align-items:center;gap:6px;">
                                        <input type="checkbox" name="use_ai" value="1"> Bật AI luận giải chuyên sâu
                                    </label>
                                @endauth
                            </div>
                        </div>

                        <div id="mayhenMarriageFormError" style="color:#85182A;font-size:13px;margin-bottom:8px;"></div>
                        <button type="submit" class="mayhen_marriage_btn" style="width:100%;">Xem Tuổi Vợ Chồng &amp; Cao Ly Ngay</button>
                    </form>
                </div>

                {{-- 2. HAI VÒNG KẾT DUYÊN SVG --}}
                <div class="mayhen_marriage_card mayhen_marriage_rings_wrap" id="mayhenMarriageRingsCard">
                    <div style="font-size:11px;letter-spacing:0.14em;color:#9A7228;font-weight:600;">MÂY HẸN · HỢP HÔN</div>
                    <h2 class="mayhen_marriage_card_title" style="color:#85182A;">Hai Vòng Kết Duyên</h2>
                    <div id="mayhenMarriageRingsWrap" style="width:100%;"></div>
                </div>
            </div>
        </div>
    </section>

    <div class="mayhen_marriage_container">
        <div id="mayhenMarriageWarnings" class="mayhen_marriage_warning_box" style="display:none;margin-top:20px;"></div>

        {{-- 5. VÒNG BÁT QUÁI BÁT TRẠCH DU NIÊN 8×8 & XEM NĂM CƯỚI --}}
        <section class="mayhen_marriage_section">
            <div class="mayhen_marriage_two_col">
                <div class="mayhen_marriage_card mayhen_cao_ly_wheel_wrap">
                    <div style="font-size:11px;letter-spacing:0.14em;color:#9A7228;font-weight:600;">MÂY HẸN · BÁT TRẠCH</div>
                    <h2 class="mayhen_marriage_card_title" style="color:#1E4D3B;">Phối Cung Bát Quái Du Niên</h2>
                    <p class="mayhen_marriage_card_sub" style="margin-bottom:10px;">Ma trận 8×8 chuẩn 64 tổ hợp. Bấm trực tiếp quẻ trên vòng tròn để đối chiếu.</p>

                    <div id="mayhenBaguaSvgWrap"></div>

                    <div class="mayhen_marriage_form_row" style="width:100%;margin-top:12px;text-align:left;">
                        <div class="mayhen_marriage_field">
                            <label for="mayhenBaguaSelectA" style="color:#1E4D3B;">Cung người thứ nhất</label>
                            <select id="mayhenBaguaSelectA" class="mayhen_marriage_select">
                                @foreach ($trigrams as $num => $t)
                                    <option value="{{ $num }}">{{ $t['trigram'] }} {{ $t['name'] }} ({{ $t['element'] }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mayhen_marriage_field">
                            <label for="mayhenBaguaSelectB" style="color:#85182A;">Cung người thứ hai</label>
                            <select id="mayhenBaguaSelectB" class="mayhen_marriage_select">
                                @foreach ($trigrams as $num => $t)
                                    <option value="{{ $num }}">{{ $t['trigram'] }} {{ $t['name'] }} ({{ $t['element'] }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div id="mayhenBaguaInfo" style="margin-top:10px;width:100%;padding:12px;background:#FAF5EB;border:1px solid var(--mayhen-cao-ly-border);border-radius:10px;text-align:left;"></div>
                </div>

                <div style="display:grid;gap:20px;">
                    <div class="mayhen_marriage_card">
                        <h2 class="mayhen_marriage_card_title">Xem Năm Kết Hôn (Kim Lâu) &amp; Chuẩn Bị Ngày Cưới</h2>
                        <div id="mayhenMarriageWeddingYearBox"></div>
                    </div>

                    <div class="mayhen_marriage_card">
                        <h2 class="mayhen_marriage_card_title">Căn Cứ Đa Phương Pháp (Evidence)</h2>
                        <p class="mayhen_marriage_card_sub">Phân biệt rõ 3 khái niệm: <em>Mệnh Nạp Âm</em> (theo năm sinh), <em>Cung Phi</em> (Bát Trạch) và <em>Nhật Chủ</em> (Bát Tự).</p>
                        <div id="mayhenMarriageEvidenceBox" style="display:grid;gap:10px;"></div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Luận giải đa phương pháp & Trao đổi với gia đình --}}
        <section class="mayhen_marriage_section">
            <div class="mayhen_marriage_card">
                <h2 class="mayhen_marriage_card_title">Luận Giải Nhân Duyên Có Căn Cứ &amp; Gợi Ý Trao Đổi Gia Đình</h2>
                <div id="mayhenMarriageAiBox" class="mayhen_marriage_two_col"></div>
            </div>
        </section>

        {{-- 12 Chủ Đề Tình Duyên --}}
        <section class="mayhen_marriage_section">
            <div class="mayhen_marriage_card">
                <h2 class="mayhen_marriage_card_title">12 Chủ Đề Tình Duyên &amp; Cuộc Sống Chung</h2>
                <p class="mayhen_marriage_card_sub">Khung chiêm nghiệm hiện đại do Mây Hẹn xây dựng giúp hai bạn chủ động đối thoại trước hôn nhân.</p>
                <div id="mayhenMarriageThemesBox" class="mayhen_marriage_three_col"></div>
            </div>
        </section>
    </div>
</div>

{{-- Màn hình phủ Animation chờ toàn trang --}}
<div id="mayhenMarriageLoadingOverlay" class="mayhen_marriage_loading_overlay" aria-hidden="true" role="status">
    <div class="mayhen_marriage_loading_card">
        <div class="mayhen_marriage_loading_seal">囍</div>
        <h3 style="font-family:var(--mayhen-marriage-serif);font-size:25px;margin:0 0 8px;">Đang Mở Sổ Nhân Duyên...</h3>
        <p style="font-size:13.5px;color:rgba(250,245,235,0.82);margin:0;">Đang đối chiếu Cao Ly Đầu Hình, Can Chi, Nạp Âm, Bát Trạch Du Niên và hoàn thiện bài luận.</p>
    </div>
</div>

<script id="mayhenMarriageInitialData" type="application/json">{!! json_encode(['analysis' => $sample, 'aiReport' => $sampleAi], JSON_UNESCAPED_UNICODE) !!}</script>
<script id="mayhenBaguaDataset" type="application/json">{!! json_encode(['trigrams' => $trigrams, 'matrix' => $duNienMatrix, 'du_nien_meta' => $duNienMeta], JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@push('scripts')
    <script src="{{ asset('assets/client/js/pages/marriage.js') }}?v={{ filemtime(public_path('assets/client/js/pages/marriage.js')) }}" defer></script>
@endpush
