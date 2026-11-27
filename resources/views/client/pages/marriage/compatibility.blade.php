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

        {{-- PILLAR SEO CONTENT CHUẨN GOOGLE E-E-A-T CHO /xem-tuoi-vo-chong --}}
        <section class="mayhen_marriage_section">
            <article class="mayhen_marriage_card mayhen_marriage_seo_article">
                <h2>Xem Tuổi Vợ Chồng Cần Đối Chiếu Những Lớp Dữ Liệu Nào Để Khách Quan?</h2>
                <p>Trong văn hóa cưới hỏi Việt Nam, <strong>xem tuổi vợ chồng</strong> không chỉ dừng lại ở việc xem hai con giáp có hợp hay xung nhau hay không. Một bản đối chiếu nghiêm túc và minh bạch cần tách bạch độc lập <strong>6 lớp tri thức truyền thống</strong>: (1) <strong>Năm sinh &amp; Can Chi</strong> theo đúng mốc Tết Nguyên Đán, (2) <strong>Thiên Can Ngũ Hợp</strong>, (3) <strong>Địa Chi</strong> (Lục Hợp, Tam Hợp, Lục Xung, Lục Hại), (4) <strong>Ngũ Hành Nạp Âm</strong> (30 Nạp Âm), (5) <strong>Cung Phi &amp; Bát Trạch Du Niên 8×8</strong>, và (6) <strong>Cao Ly Đầu Hình</strong> (Can chồng – Chi vợ).</p>
                <p>Tại <strong>Mây Hẹn – Duyên Hợp</strong>, mỗi lớp được tính toán độc lập bằng thuật toán xác định và trình bày rõ căn cứ — tuyệt đối không cộng trừ máy móc thành những con số cảm tính như *"hợp nhau 85%"* hay *"khắc nhau 70%"*.</p>

                <h2>Phân Biệt 3 Khái Niệm "Mệnh" Dễ Bị Nhầm Lẫn Nhất Khi Xem Tuổi Kết Hôn</h2>
                <p>Rất nhiều cặp đôi hoang mang khi thấy nơi này ghi mình mệnh Thổ, nơi khác lại ghi mệnh Thủy hoặc mệnh Mộc. Thực chất đó là <strong>3 khái niệm hoàn toàn khác nhau</strong> của cổ học phương Đông:</p>

                <div class="mayhen_marriage_seo_table_wrap">
                    <table class="mayhen_marriage_seo_table">
                        <thead>
                            <tr>
                                <th>Khái niệm Mệnh</th>
                                <th>Căn cứ tính toán</th>
                                <th>Ví dụ: Nam 1990 (Canh Ngọ)</th>
                                <th>Hệ thống áp dụng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>1. Mệnh Nạp Âm</strong></td>
                                <td>Can Chi năm sinh (chu kỳ 60 Hoa Giáp)</td>
                                <td><strong>Lộ Bàng Thổ</strong> (hành Thổ)</td>
                                <td>Xem tuổi dân gian, đối chiếu sinh khắc năm sinh</td>
                            </tr>
                            <tr>
                                <td><strong>2. Cung Phi (Bát Trạch)</strong></td>
                                <td>Năm sinh Âm lịch + Giới tính (8 quẻ Bát Quái)</td>
                                <td><strong>Cung Khảm</strong> (hành Thủy · Đông Tứ Mệnh)</td>
                                <td>Phối cung vợ chồng Bát Trạch Du Niên 8×8</td>
                            </tr>
                            <tr>
                                <td><strong>3. Nhật Chủ (Bát Tự)</strong></td>
                                <td>Thiên Can của <em>ngày sinh</em> (không phải năm sinh)</td>
                                <td>Tùy ngày sinh (vd: Giáp Mộc, Bính Hỏa...)</td>
                                <td><a href="{{ route('bazi.index') }}" style="color:#1E4D3B;font-weight:600;">Lập lá số Bát Tự – Tứ Trụ</a> chuyên sâu</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h2>Sự Thật Về "Tứ Hành Xung" Và Lỗi Sai Phổ Biến Khi Xem Tuổi Con Giáp</h2>
                <p>Một hiểu lầm rất phổ biến là hễ hai tuổi nằm trong cùng bộ <em>Tứ Hành Xung</em> (<em>Dần–Thân–Tỵ–Hợi</em>, <em>Thìn–Tuất–Sửu–Mùi</em>, <em>Tý–Ngọ–Mão–Dậu</em>) thì đều xung khắc nặng và không nên kết hôn. Thực tế theo phép Địa Chi chuẩn:</p>
                <ul>
                    <li>Chỉ có <strong>6 cặp Lục Xung trực diện</strong>: Tý–Ngọ, Sửu–Mùi, Dần–Thân, Mão–Dậu, Thìn–Tuất, Tỵ–Hợi.</li>
                    <li>Ngược lại, <strong>Dần và Hợi</strong> tuy cùng nằm trong nhóm Tứ Hành Xung dân gian nhưng lại là một cặp <strong>Lục Hợp</strong> rất hài hòa!</li>
                    <li>Ngay cả với cặp Lục Xung, ý nghĩa gốc chỉ là sự khác biệt rõ về nhịp sống và cách phản ứng — khi hai người hiểu sớm để thống nhất nguyên tắc giao tiếp, sự khác biệt lại trở thành mảnh ghép bổ sung nhau.</li>
                </ul>

                <h2>Cách Tính Cung Phi Bát Trạch &amp; Ma Trận Du Niên 8×8 Chuẩn Xác Mọi Thế Kỷ</h2>
                <p>Khác với công thức rút gọn 2 chữ số cuối vốn hay bị tính sai cho thế hệ sinh từ năm 2000 trở đi, Mây Hẹn áp dụng công thức tổng rút gọn cả 4 chữ số năm sinh Âm lịch (`r`): Nam lấy `11 − r` rút gọn (gặp số 5 quy về <strong>Khôn</strong>), Nữ lấy `4 + r` rút gọn (gặp số 5 quy về <strong>Cấn</strong>). Sau đó hai cung được đối chiếu chính xác trên <strong>Ma trận 64 tổ hợp Bát Trạch</strong> để xác định 8 trạng thái Du Niên: 4 nhóm Cát (<em>Sinh Khí, Thiên Y, Diên Niên, Phục Vị</em>) và 4 nhóm Cần Lưu Tâm (<em>Họa Hại, Lục Sát, Ngũ Quỷ, Tuyệt Mệnh</em>).</p>

                <h2>Xem Năm Kết Hôn (Tuổi Kim Lâu) &amp; Kết Nối Kế Hoạch Cưới Hỏi</h2>
                <p>Theo phong tục cưới hỏi miền Bắc, gia đình thường tính <strong>tuổi mụ cô dâu chia 9</strong> (nếu dư `1, 3, 6, 8` gọi là Kim Lâu). Tuy nhiên ở nhiều vùng miền và gia đình hiện đại, yếu tố sức khỏe, tài chính, công việc và sự thuận tiện của hai bên nội ngoại luôn được đặt lên hàng đầu; đồng thời quy tắc <em>Hoang Ốc</em> chủ yếu gắn với xây dựng nhà cửa nên không áp đặt máy móc vào việc cưới hỏi.</p>
                <div class="mayhen_marriage_seo_callout">
                    Khi hai bạn đã thống nhất năm cưới, hãy tiếp tục hành trình chuẩn bị nhẹ nhàng tại Mây Hẹn: tra ngày cưới cuối tuần thuận lợi ở <a href="{{ route('calendar.index') }}" style="color:#1E4D3B;font-weight:600;">Lịch Vạn Niên Mây Hẹn</a>, khám phá chiều sâu tính cách qua <a href="{{ route('bazi.love') }}" style="color:#1E4D3B;font-weight:600;">Bát Tự Tình Duyên</a> hoặc <a href="{{ route('tuvi.synastry.index') }}" style="color:#1E4D3B;font-weight:600;">Tử Vi Song Mệnh</a>, và chọn mẫu <a href="{{ route('templates.index') }}" style="color:#85182A;font-weight:600;">Thiệp Cưới Online</a> tinh tế cho ngày trọng đại.
                </div>

                <h2>Câu Hỏi Thường Gặp Khi Xem Tuổi Vợ Chồng &amp; Kết Hôn (FAQ)</h2>
                <div class="mayhen_marriage_faq_list">
                    <details class="mayhen_marriage_faq_item" open>
                        <summary>Vì sao xem Nạp Âm thấy hợp nhưng Cung Phi Bát Trạch hoặc Cao Ly lại báo cần lưu tâm?</summary>
                        <p>Mỗi hệ thống sử dụng một quy tắc khác nhau: Nạp Âm xét Ngũ Hành năm sinh, Cung Phi xét quẻ Bát Quái theo giới tính, còn Cao Ly xét Can chồng – Chi vợ. Việc các lớp cho kết quả đan xen là hoàn toàn tự nhiên; bạn nên xem toàn cảnh để hiểu điểm mạnh và điểm cần chăm chút thay vì chỉ bám vào một kết quả đơn lẻ.</p>
                    </details>
                    <details class="mayhen_marriage_faq_item">
                        <summary>Hai người cùng năm sinh (bằng tuổi) có lấy nhau được không?</summary>
                        <p>Hoàn toàn được. Cặp đôi đồng tuổi có cùng Nạp Âm và cùng Địa Chi (thường đồng điệu thế hệ và góc nhìn sống), còn Cung Phi sẽ khác nhau do khác giới tính nam/nữ. Điểm quan trọng nhất với cặp đôi bằng tuổi là sự nhường nhịn khi cả hai cùng có cái tôi mạnh.</p>
                    </details>
                    <details class="mayhen_marriage_faq_item">
                        <summary>Phạm tuổi Kim Lâu có nhất thiết phải hoãn cưới sang năm khác không?</summary>
                        <p>Không bắt buộc. Nhiều gia đình linh hoạt tổ chức sau tiết Đông Chí, làm lễ đơn giản ấm cúng hoặc ưu tiên thời điểm công việc – tài chính của hai bạn đã sẵn sàng nhất. Sự đồng thuận và tôn trọng giữa hai bên gia đình mới là yếu tố tạo nên ngày cưới trọn vẹn.</p>
                    </details>
                    <details class="mayhen_marriage_faq_item">
                        <summary>Làm sao để trao đổi khéo léo nếu bố mẹ lo lắng chuyện tuổi xung khắc?</summary>
                        <p>Bạn có thể lưu kết quả vào <strong>Sổ Nhân Duyên</strong> và dùng <em>Trợ Lý Trao Đổi Với Gia Đình</em> của Mây Hẹn để trình bày kết quả đa phương pháp một cách điềm đạm, tôn trọng niềm tin của người lớn đồng thời chứng minh sự trưởng thành và kế hoạch hôn nhân nghiêm túc của hai bạn.</p>
                    </details>
                </div>
            </article>
        </section>
    </div>
</div>

@php
    $compatSchema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebApplication',
                'name' => 'Xem Tuổi Vợ Chồng & Tra Cứu Tuổi Kết Hôn Online Miễn Phí — Mây Hẹn',
                'url' => $pageMeta['canonical'],
                'applicationCategory' => 'LifestyleApplication',
                'operatingSystem' => 'All',
                'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'VND'],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang Chủ', 'item' => route('home')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Xem Tuổi Vợ Chồng', 'item' => route('marriage.compatibility.index')],
                ],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => [
                    ['@type' => 'Question', 'name' => 'Vì sao xem Nạp Âm thấy hợp nhưng Cung Phi Bát Trạch hoặc Cao Ly lại báo cần lưu tâm?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Mỗi phương pháp cổ học dùng một lớp dữ liệu khác nhau (Nạp Âm năm sinh, Quẻ Cung Phi theo giới tính, Can chồng – Chi vợ Cao Ly) nên kết quả đan xen là hoàn toàn bình thường.']],
                    ['@type' => 'Question', 'name' => 'Hai người cùng năm sinh (bằng tuổi) có lấy nhau được không?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Hoàn toàn được. Cặp đôi đồng tuổi thường đồng điệu thế hệ và góc nhìn sống; sự nhường nhịn và thấu hiểu là chìa khóa cho hôn nhân bền vững.']],
                    ['@type' => 'Question', 'name' => 'Phạm tuổi Kim Lâu có nhất thiết phải hoãn cưới sang năm khác không?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Không bắt buộc. Tuổi Kim Lâu mang tính phong tục tham khảo theo vùng miền; nhiều gia đình linh hoạt chọn thời điểm thuận lợi nhất về công việc, tài chính và lịch hai nhà.']],
                    ['@type' => 'Question', 'name' => 'Làm sao để trao đổi khéo léo nếu bố mẹ lo lắng chuyện tuổi xung khắc?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Hãy lắng nghe tôn trọng, trình bày bức tranh đa phương pháp khách quan và chia sẻ kế hoạch tài chính, công việc rõ ràng để gia đình hai bên hoàn toàn yên tâm.']],
                ],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($compatSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

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
    <script type="module" src="{{ asset_v('assets/client/js/pages/marriage.js') }}"></script>
@endpush
