@extends('client.layouts.master')

@section('title', $pageMeta['title'])
@section('meta_description', $pageMeta['meta_description'])
@section('canonical', $pageMeta['canonical'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/bazi.css') }}?v={{ filemtime(public_path('assets/client/css/pages/bazi.css')) }}">
@endpush

@section('content')
<div class="mayhen_bazi_page">
    {{-- HERO: FORM LẬP LÁ SỐ + KHÔNG GIAN TỨ TRỤ 3D --}}
    <section class="mayhen_bazi_hero">
        <div class="mayhen_bazi_container">
            <div class="mayhen_bazi_eyebrow">✦ MÂY HẸN – TỨ TRỤ NHÂN SINH · CHUẨN TỬ BÌNH &amp; THIÊN VĂN VIỆT NAM</div>
            <h1 class="mayhen_bazi_h1">{{ $pageMeta['h1'] }}</h1>
            <p class="mayhen_bazi_lead">
                Hiểu bản thân, thấu hiểu nhân duyên, vun đắp cuộc sống. Lập lá số Bát Tự xác định 100% độc lập AI — đổi năm tại Lập Xuân, đổi tháng theo 12 mốc Tiết, hỗ trợ Giờ Mặt Trời Thực (True Solar Time), Tàng Can, Thập Thần, Cách Cục, Dụng Thần đa phương pháp và Đại Vận miễn phí.
            </p>

            <nav class="mayhen_bazi_quick_nav" aria-label="Phân mục Bát Tự">
                <a href="{{ route('bazi.index') }}" class="mayhen_bazi_pill {{ $topic === null ? 'is-active' : '' }}">Lập Lá Số Tứ Trụ</a>
                <a href="{{ route('bazi.love') }}" class="mayhen_bazi_pill">Bát Tự Tình Duyên</a>
                <a href="{{ route('bazi.topic', ['topic' => 'dung-than']) }}" class="mayhen_bazi_pill {{ $topic === 'dung-than' ? 'is-active' : '' }}">Dụng Thần</a>
                <a href="{{ route('bazi.topic', ['topic' => 'nhat-chu']) }}" class="mayhen_bazi_pill {{ $topic === 'nhat-chu' ? 'is-active' : '' }}">Nhật Chủ</a>
                <a href="{{ route('bazi.topic', ['topic' => 'thap-than']) }}" class="mayhen_bazi_pill {{ $topic === 'thap-than' ? 'is-active' : '' }}">Thập Thần</a>
                <a href="{{ route('bazi.topic', ['topic' => 'dai-van']) }}" class="mayhen_bazi_pill {{ $topic === 'dai-van' ? 'is-active' : '' }}">Đại Vận &amp; Lưu Niên</a>
                <a href="{{ route('bazi.reverse') }}" class="mayhen_bazi_pill">Tra Cứu Ngược</a>
                <a href="{{ route('bazi.library') }}" class="mayhen_bazi_pill">Bách Khoa Bát Tự</a>
                @auth
                    <a href="{{ route('bazi.profiles') }}" class="mayhen_bazi_pill">Hồ Sơ Của Tôi</a>
                @endauth
            </nav>

            <div class="mayhen_bazi_hero_grid">
                {{-- Form Lập Lá Số --}}
                <div class="mayhen_bazi_card">
                    <h2 class="mayhen_bazi_card_title">Thông Tin Lập Lá Số Tứ Trụ</h2>
                    <p class="mayhen_bazi_card_sub">Tra cứu cơ bản miễn phí, không bắt buộc đăng nhập. Lưu hồ sơ, AI luận giải 10 tầng &amp; công cụ nâng cao áp dụng theo gói dịch vụ.</p>

                    {{-- Thanh trạng thái gói cước & hạn mức --}}
                    <div class="mayhen_bazi_plan_bar" id="mayhenBaziPlanBar">
                        @if ($baziQuota['is_logged_in'])
                            <div>
                                <span class="mayhen_bazi_badge">Gói {{ $baziQuota['plan_label'] }}</span>
                                <span id="mayhenBaziQuotaText" style="font-size:12.5px;color:#4E342E;margin-left:6px;">
                                    Còn <strong>{{ $baziQuota['remaining_today'] }}/{{ $baziQuota['daily_limit'] }}</strong> lượt lưu &amp; AI hôm nay · {{ $baziQuota['cooldown_label'] }} · Hỏi sâu tối đa {{ $baziQuota['max_followups'] }} câu/hồ sơ
                                </span>
                            </div>
                            @if ($baziQuota['plan_slug'] === 'premium')
                                <a href="{{ route('pricing') }}" style="font-size:12px;font-weight:600;color:#85182A;text-decoration:none;">Nâng cấp Plus/Pro &rarr;</a>
                            @endif
                        @else
                            <div style="font-size:12.5px;color:#4E342E;">
                                <strong>Chế độ Khách:</strong> Lập lá số cơ bản miễn phí. <a href="{{ route('login') }}" style="color:#1E4D3B;font-weight:600;">Đăng nhập (Premium 0đ)</a> để nhận 5 lượt lưu hồ sơ &amp; AI 10 tầng/ngày.
                            </div>
                        @endif
                    </div>

                    <form id="mayhenBaziForm" data-endpoint="{{ route('bazi.calculate') }}">
                        <div class="mayhen_bazi_form_grid mayhen_bazi_form_grid--2">
                            <div class="mayhen_bazi_field">
                                <label for="baziName">Họ tên hoặc biệt danh (tùy chọn)</label>
                                <input id="baziName" name="name" type="text" class="mayhen_bazi_input" placeholder="Ví dụ: Minh Anh">
                            </div>
                            <div class="mayhen_bazi_field">
                                <label for="baziGender">Phân loại Đại Vận (tùy chọn)</label>
                                <select id="baziGender" name="gender" class="mayhen_bazi_select">
                                    <option value="female">Nữ (theo quy ước Đại Vận)</option>
                                    <option value="male">Nam (theo quy ước Đại Vận)</option>
                                    <option value="unspecified">Không chọn (xem cả 2 chiều)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mayhen_bazi_form_grid">
                            <div class="mayhen_bazi_field">
                                <label for="mayhenBaziCalendarType">Loại lịch</label>
                                <select id="mayhenBaziCalendarType" name="calendar_type" class="mayhen_bazi_select">
                                    <option value="solar">Dương lịch</option>
                                    <option value="lunar">Âm lịch Việt Nam</option>
                                </select>
                            </div>
                            <div class="mayhen_bazi_field">
                                <label for="baziDay">Ngày</label>
                                <input id="baziDay" name="day" type="number" min="1" max="31" value="15" class="mayhen_bazi_input" required>
                            </div>
                            <div class="mayhen_bazi_field">
                                <label for="baziMonth">Tháng</label>
                                <input id="baziMonth" name="month" type="number" min="1" max="12" value="5" class="mayhen_bazi_input" required>
                            </div>
                        </div>

                        <div class="mayhen_bazi_form_grid">
                            <div class="mayhen_bazi_field">
                                <label for="baziYear">Năm (1900–2100)</label>
                                <input id="baziYear" name="year" type="number" min="1900" max="2100" value="1995" class="mayhen_bazi_input" required>
                            </div>
                            <div class="mayhen_bazi_field">
                                <label for="mayhenBaziTimeAccuracy">Độ chính xác giờ sinh</label>
                                <select id="mayhenBaziTimeAccuracy" name="time_accuracy" class="mayhen_bazi_select">
                                    <option value="exact">Biết chính xác</option>
                                    <option value="approx">Gần đúng</option>
                                    <option value="window">Khoảng giờ</option>
                                    <option value="session">Chỉ biết buổi</option>
                                    <option value="unknown">Không biết giờ sinh</option>
                                </select>
                            </div>
                            <div class="mayhen_bazi_field" id="mayhenBaziHourInputs">
                                <label for="baziHour">Giờ : Phút (24h)</label>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                                    <input id="baziHour" name="hour" type="number" min="0" max="23" value="9" class="mayhen_bazi_input" aria-label="Giờ sinh">
                                    <input name="minute" type="number" min="0" max="59" value="30" class="mayhen_bazi_input" aria-label="Phút sinh">
                                </div>
                            </div>
                        </div>

                        <div class="mayhen_bazi_form_grid mayhen_bazi_form_grid--2">
                            <div class="mayhen_bazi_field">
                                <label for="baziLocation">Địa điểm sinh (Kinh độ &amp; Múi giờ)</label>
                                <select id="baziLocation" name="location_key" class="mayhen_bazi_select">
                                    @foreach ($locations as $key => $loc)
                                        <option value="{{ $key }}" @selected($key === 'ha-noi')>{{ $loc['name'] }} ({{ $loc['country'] }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mayhen_bazi_field">
                                <label for="baziMethod">Phương pháp lập lá số (Method Profile)</label>
                                <select id="baziMethod" name="method_id" class="mayhen_bazi_select">
                                    @foreach ($methods as $mid => $m)
                                        @php
                                            $isPlusMethod = in_array($mid, ['tu_binh_true_solar_v1', 'tu_binh_lmst_v1'], true);
                                        @endphp
                                        <option value="{{ $mid }}">{{ $m['name'] }}@if ($isPlusMethod && ! $baziQuota['allow_solar_time']) [Gói Plus+]@endif</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mayhen_bazi_check_row">
                            <label id="mayhenBaziLeapWrap" style="display:none;align-items:center;gap:6px;">
                                <input type="checkbox" name="is_leap_month" value="1"> Tháng nhuận Âm lịch
                            </label>
                            @auth
                                <label style="display:inline-flex;align-items:center;gap:6px;">
                                    <input type="checkbox" name="save_profile" value="1" checked> Lưu vào Hồ Sơ Cá Nhân
                                </label>
                                <label style="display:inline-flex;align-items:center;gap:6px;">
                                    <input type="checkbox" name="use_ai" value="1"> Bật AI luận giải 10 tầng
                                </label>
                            @endauth
                        </div>

                        <div id="mayhenBaziFormError" style="color:#85182A;font-size:13px;margin-bottom:8px;"></div>
                        <div id="mayhenBaziSavedNotice" style="display:none;margin-bottom:10px;padding:10px 12px;background:rgba(30,77,59,0.08);border-radius:10px;font-size:13px;"></div>

                        <div style="display:flex;flex-wrap:wrap;gap:10px;">
                            <button type="submit" class="mayhen_bazi_btn" style="flex:1;">Lập Lá Số Tứ Trụ Ngay</button>
                            <button type="button" id="mayhenBaziCompareBtn" data-endpoint="{{ route('bazi.compare') }}" class="mayhen_bazi_btn mayhen_bazi_btn--outline">
                                So Sánh Trường Phái @if (! $baziQuota['allow_compare_methods']) <span style="font-size:11px;color:#85182A;">(Plus+)</span> @endif
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Không Gian Tứ Trụ 3D & Bảng 2D --}}
                <div class="mayhen_bazi_stage_card" id="mayhenBaziStageCard">
                    <div class="mayhen_bazi_stage_top">
                        <div>
                            <div style="font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:#CFA860;">KHÔNG GIAN TỨ TRỤ 3D</div>
                            <div id="mayhenBaziStageMeta" style="font-size:13px;color:rgba(250,245,235,0.82);margin-top:2px;">Đang hiển thị lá số minh họa</div>
                        </div>
                        <div class="mayhen_bazi_stage_controls">
                            <button type="button" class="mayhen_bazi_stage_btn is-active" data-bazi-view="3d">Tứ Trụ 3D</button>
                            <button type="button" class="mayhen_bazi_stage_btn" data-bazi-view="2d">Bảng 2D</button>
                            <label style="font-size:11.5px;color:#F4DCAA;display:inline-flex;align-items:center;gap:6px;">
                                Xoay 3D
                                <input id="mayhenBaziTiltRange" type="range" min="-18" max="18" value="0" style="width:74px;">
                            </label>
                        </div>
                    </div>

                    <div id="mayhenBaziViewport3D" class="mayhen_bazi_pillars_viewport">
                        <div id="mayhenBaziPillars3D" class="mayhen_bazi_pillars_3d"></div>
                    </div>

                    <div id="mayhenBaziTable2D" class="mayhen_bazi_table_wrap" style="display:none;color:#292524;"></div>

                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-top:12px;padding-top:12px;border-top:1px solid rgba(250,245,235,0.16);">
                        <span style="font-size:12px;color:rgba(250,245,235,0.75);">Xuất bản nghệ thuật (ẩn ngày giờ sinh):</span>
                        <div style="display:flex;gap:6px;">
                            <button type="button" class="mayhen_bazi_stage_btn" data-bazi-export="1:1">PNG 1:1</button>
                            <button type="button" class="mayhen_bazi_stage_btn" data-bazi-export="4:5">PNG 4:5</button>
                            <button type="button" class="mayhen_bazi_stage_btn" data-bazi-export="9:16">PNG 9:16</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="mayhenBaziCompareResult" style="margin-top:16px;"></div>
        </div>
    </section>

    {{-- KẾT QUẢ PHÂN TÍCH CHUYÊN SÂU --}}
    <div class="mayhen_bazi_container">
        <div id="mayhenBaziWarnings" class="mayhen_bazi_warning_box" style="display:none;margin-top:20px;"></div>

        {{-- Chi tiết Thời gian sinh, Lập Xuân & Giờ Mặt Trời --}}
        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_card">
                <h2 class="mayhen_bazi_card_title">Căn Cứ Thời Gian Sinh, Lập Xuân &amp; Giờ Mặt Trời</h2>
                <div id="mayhenBaziTimeDetail"></div>
            </div>
        </section>

        {{-- Ngũ Hành 3 Chế Độ & Thập Thần Orbit --}}
        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_two_col">
                <div class="mayhen_bazi_card">
                    <h2 class="mayhen_bazi_card_title">Phân Tích Ngũ Hành (3 Chế Độ Độc Lập)</h2>
                    <div class="mayhen_bazi_tabs">
                        <button type="button" class="mayhen_bazi_tab_btn mayhen_bazi_el_mode_btn is-active" data-el-mode="eight_chars">8 Chữ</button>
                        <button type="button" class="mayhen_bazi_tab_btn mayhen_bazi_el_mode_btn" data-el-mode="with_hidden">Mở Rộng Tàng Can</button>
                        <button type="button" class="mayhen_bazi_tab_btn mayhen_bazi_el_mode_btn" data-el-mode="seasonal_qi">Khí Thế Nguyệt Lệnh</button>
                    </div>
                    <div id="mayhenBaziElementsSvg" class="mayhen_bazi_svg_box"></div>
                    <div id="mayhenBaziElementsBars"></div>
                </div>

                <div class="mayhen_bazi_card">
                    <h2 class="mayhen_bazi_card_title">Thập Thần Orbit (Xoay Quanh Nhật Chủ)</h2>
                    <p class="mayhen_bazi_card_sub">Bấm vào từng Thập Thần để xem định nghĩa và vị trí xuất hiện trong lá số (phân biệt rõ hiện diện vs thư viện).</p>
                    <div id="mayhenBaziTenGodsSvg" class="mayhen_bazi_svg_box"></div>
                    <div id="mayhenBaziTenGodsDetail"></div>
                </div>
            </div>
        </section>

        {{-- Cách Cục, Dụng Thần Đa Phương Pháp & Thần Sát --}}
        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_two_col">
                <div class="mayhen_bazi_card">
                    <h2 class="mayhen_bazi_card_title">Cách Cục, Quan Hệ Can Chi &amp; Dụng Thần Đa Phương Pháp</h2>
                    <div id="mayhenBaziStructureBox"></div>
                </div>
                <div class="mayhen_bazi_card">
                    <h2 class="mayhen_bazi_card_title">Thần Sát Bát Tự &amp; Thai Nguyên / Mệnh Cung</h2>
                    <p class="mayhen_bazi_card_sub">Lớp thông tin bổ sung theo Tam Mệnh Thông Hội (phân biệt hoàn toàn với hệ thống sao của Tử Vi Đẩu Số).</p>
                    <div id="mayhenBaziStarsBox"></div>
                </div>
            </div>
        </section>

        {{-- Timeline Đại Vận & Time Travel Lưu Niên / Lưu Nguyệt --}}
        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_card">
                <h2 class="mayhen_bazi_card_title">Timeline Đại Vận 10 Năm &amp; Time Travel (Lưu Niên · Lưu Nguyệt)</h2>
                <div id="mayhenBaziLuckBox" style="margin-bottom:20px;"></div>

                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding-top:16px;border-top:1px solid var(--mayhen-bazi-border);margin-bottom:14px;">
                    <label for="mayhenBaziAnnualYear" style="font-weight:600;font-size:13.5px;">Chọn năm Time Travel (Lưu Niên &amp; 12 Lưu Nguyệt):</label>
                    <input id="mayhenBaziAnnualYear" type="number" min="1900" max="2100" value="{{ now('Asia/Ho_Chi_Minh')->year }}" data-endpoint="{{ route('bazi.annual_flow') }}" class="mayhen_bazi_input" style="width:120px;">
                    <span style="font-size:12.5px;color:#6E6259;">(Không làm thay đổi 4 trụ gốc)</span>
                </div>
                <div id="mayhenBaziAnnualBox"></div>
            </div>
        </section>

        {{-- Cây Lập Luận (Evidence Graph) --}}
        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_card">
                <h2 class="mayhen_bazi_card_title">Cây Lập Luận &amp; Căn Cứ Quy Tắc (Evidence Graph)</h2>
                <p class="mayhen_bazi_card_sub">Mọi nhận định của hệ thống và AI đều truy xuất được về đúng Rule ID, Can Chi và tác phẩm cổ điển tương ứng.</p>
                <div id="mayhenBaziEvidenceBox" class="mayhen_bazi_evidence_list"></div>
            </div>
        </section>

        {{-- Báo Cáo Luận Giải 10 Tầng --}}
        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_card">
                <h2 class="mayhen_bazi_card_title">Luận Giải Bát Tự 10 Tầng Có Căn Cứ</h2>
                <div id="mayhenBaziReportBox" class="mayhen_bazi_report_grid"></div>
            </div>
        </section>

        {{-- Nội Dung Kiến Thức Chuẩn SEO & Liên Kết Hệ Sinh Thái Mây Hẹn --}}
        <section class="mayhen_bazi_section">
            <div class="mayhen_bazi_card">
                <h2 class="mayhen_bazi_card_title">Bát Tự – Tứ Trụ Là Gì? Phân Biệt Với Tử Vi &amp; Nạp Âm Năm Sinh</h2>
                <div style="font-size:14px;line-height:1.72;color:var(--mayhen-bazi-ink);display:grid;gap:12px;">
                    <p><strong>Bát Tự (Tứ Trụ)</strong> là hệ thống mệnh lý cổ học phương Đông dựa trên 4 trụ thời gian sinh: <em>Trụ Năm, Trụ Tháng, Trụ Ngày và Trụ Giờ</em> (gồm 4 Thiên Can và 4 Địa Chi, hợp thành 8 chữ). Trung tâm của toàn bộ lá số là <strong>Nhật Chủ (Thiên Can Trụ Ngày)</strong> — hoàn toàn khác với việc chỉ xem mệnh Nạp Âm theo năm sinh hay xem tuổi con giáp.</p>
                    <p>Khác với <a href="{{ route('tuvi.index') }}" style="color:#1E4D3B;font-weight:600;">Tử Vi Đẩu Số</a> (an hơn 100 sao vào 12 cung theo ngày giờ Âm lịch), Bát Tự tính Trụ Năm theo <strong>thời điểm Lập Xuân</strong> và Trụ Tháng theo <strong>12 mốc Tiết Khí</strong> trên quỹ đạo Mặt Trời. Bạn cũng có thể đối chiếu ngày âm dương chuẩn Việt Nam tại <a href="{{ route('calendar.index') }}" style="color:#1E4D3B;font-weight:600;">Vạn Niên Lịch Mây Hẹn</a> hoặc khám phá thêm <a href="{{ route('bazi.love') }}" style="color:#85182A;font-weight:600;">Bát Tự Tình Duyên &amp; Hôn Nhân</a> trước khi chuẩn bị <a href="{{ route('templates.index') }}" style="color:#1E4D3B;font-weight:600;">Thiệp Cưới Online</a>.</p>
                </div>
            </div>
        </section>
    </div>
</div>

<script id="mayhenBaziInitialData" type="application/json">{!! json_encode(['chart' => $sampleChart, 'report' => $sampleReport], JSON_UNESCAPED_UNICODE) !!}</script>

{{-- MÀN HÌNH PHỦ ANIMATION CHỜ TOÀN TRANG (60fps GPU, KHÓA TƯƠNG TÁC CHỐNG BẤM NHẦM) --}}
<div id="mayhenBaziLoadingOverlay" class="mayhen_bazi_loading_overlay" aria-hidden="true" role="status" aria-live="polite">
    <div class="mayhen_bazi_loading_card">
        <div class="mayhen_bazi_loading_emblem" aria-hidden="true">
            <div class="mayhen_bazi_loading_ring mayhen_bazi_loading_ring--outer"></div>
            <div class="mayhen_bazi_loading_ring mayhen_bazi_loading_ring--inner"></div>
            <span class="mayhen_bazi_loading_kanji">八字</span>
        </div>
        <h3 id="mayhenBaziLoadingTitle" class="mayhen_bazi_loading_title">Đang Khởi Lập Lá Số Tứ Trụ...</h3>
        <p id="mayhenBaziLoadingSub" class="mayhen_bazi_loading_sub">Hệ thống đang đối chiếu Tiết Khí thiên văn và quy tắc Tử Bình.</p>
        <ol class="mayhen_bazi_loading_steps" id="mayhenBaziLoadingSteps">
            <li data-step="0"><span>1</span> Quy đổi Lịch Pháp Việt Nam &amp; 12 mốc Tiết Khí</li>
            <li data-step="1"><span>2</span> An Tứ Trụ, Tàng Can &amp; Ma trận Thập Thần</li>
            <li data-step="2"><span>3</span> Phân tích Nguyệt Lệnh, Cách Cục &amp; Dụng Thần</li>
            <li data-step="3"><span>4</span> Hoàn thiện Luận Giải 10 Tầng &amp; Lưu Hồ Sơ</li>
        </ol>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/client/js/pages/bazi.js') }}?v={{ filemtime(public_path('assets/client/js/pages/bazi.js')) }}" defer></script>
@endpush
